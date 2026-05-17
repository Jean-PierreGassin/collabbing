#!/usr/bin/env bash
set -euo pipefail

MODE="${1:-deploy}"
STACK_NAME="${STACK_NAME:-collabbing}"
STACK_FILE="${STACK_FILE:-docker/production/stack.yaml}"
APP_ENV_FILE="${APP_ENV_FILE:-.env.production}"
DOCKER_ENV_FILE="${DOCKER_ENV_FILE:-docker/production/${APP_ENV_FILE}}"
IMAGE="${IMAGE:?Set IMAGE to the production image tag to deploy.}"
NETWORK_NAME="${STACK_NAME}_app_network"
MIGRATED_BEFORE_DEPLOY=0

export APP_ENV_FILE IMAGE

if [ "$MODE" != "deploy" ] && [ "$MODE" != "--preflight" ]; then
  echo "Usage: $0 [--preflight]" >&2
  exit 1
fi

fail_preflight() {
  echo "Production deploy preflight failed: $*" >&2
  exit 1
}

load_env_file() {
  local line
  local name
  local value

  if [ ! -f "$DOCKER_ENV_FILE" ]; then
    echo "Missing production environment file: $DOCKER_ENV_FILE" >&2
    exit 1
  fi

  while IFS= read -r line || [ -n "$line" ]; do
    line="${line%$'\r'}"

    case "$line" in
      "" | \#*)
        continue
        ;;
    esac

    name="${line%%=*}"
    value="${line#*=}"

    if [ "$name" = "$line" ] || ! [[ "$name" =~ ^[A-Za-z_][A-Za-z0-9_]*$ ]]; then
      fail_preflight "invalid environment line in $DOCKER_ENV_FILE: $line"
    fi

    export "$name=$value"
  done < "$DOCKER_ENV_FILE"
}

require_env() {
  local name="$1"
  local value="${!name:-}"

  if [ -z "$value" ]; then
    fail_preflight "missing $name"
  fi
}

require_secret() {
  local name="$1"
  local value="${!name:-}"

  case "$value" in
    "" | change-me | change-me-* | secret | root | password | example | placeholder)
      fail_preflight "$name must be set to a real production value"
      ;;
  esac
}

validate_production_env() {
  for name in \
    APP_ENV \
    APP_KEY \
    APP_DEBUG \
    APP_URL \
    DB_CONNECTION \
    DB_HOST \
    DB_DATABASE \
    DB_USERNAME \
    DB_PASSWORD \
    MYSQL_DATABASE \
    MYSQL_USER \
    MYSQL_PASSWORD \
    MYSQL_ROOT_PASSWORD \
    CACHE_DRIVER \
    SESSION_DRIVER \
    SESSION_SECURE_COOKIE \
    QUEUE_CONNECTION \
    REDIS_HOST; do
    require_env "$name"
  done

  [ "$APP_ENV" = "production" ] || fail_preflight "APP_ENV must be production"
  [ "$APP_DEBUG" = "false" ] || fail_preflight "APP_DEBUG must be false"
  [ "$DB_CONNECTION" = "mysql" ] || fail_preflight "DB_CONNECTION must be mysql"
  [ "$CACHE_DRIVER" = "redis" ] || fail_preflight "CACHE_DRIVER must be redis"
  [ "$SESSION_DRIVER" = "redis" ] || fail_preflight "SESSION_DRIVER must be redis"
  [ "$SESSION_SECURE_COOKIE" = "true" ] || fail_preflight "SESSION_SECURE_COOKIE must be true"
  [ "$QUEUE_CONNECTION" = "redis" ] || fail_preflight "QUEUE_CONNECTION must be redis"

  case "$APP_URL" in
    https://*)
      ;;
    *)
      fail_preflight "APP_URL must use https"
      ;;
  esac

  case "$APP_KEY" in
    base64:?*)
      ;;
    *)
      fail_preflight "APP_KEY must be generated"
      ;;
  esac

  require_secret DB_PASSWORD
  require_secret MYSQL_PASSWORD
  require_secret MYSQL_ROOT_PASSWORD
}

run_preflight() {
  [ -f "$STACK_FILE" ] || fail_preflight "missing stack file: $STACK_FILE"
  validate_production_env
  docker info >/dev/null || fail_preflight "Docker is not reachable"

  if docker info --format '{{.Swarm.LocalNodeState}}' | grep -qx active; then
    echo "Docker Swarm is active."
  else
    echo "Docker Swarm is not active; deploy will initialize it."
  fi

  echo "Production deploy preflight passed."
}

load_env_file

if [ "$MODE" = "--preflight" ]; then
  run_preflight
  exit 0
fi

validate_production_env

if ! docker info --format '{{.Swarm.LocalNodeState}}' | grep -qx active; then
  docker swarm init
fi

docker pull "$IMAGE"

run_artisan() {
  docker run --rm \
    --network "$NETWORK_NAME" \
    --env-file "$DOCKER_ENV_FILE" \
    "$IMAGE" \
    php artisan "$@"
}

run_artisan_with_retry() {
  local attempts="${ARTISAN_RETRY_ATTEMPTS:-30}"
  local delay="${ARTISAN_RETRY_DELAY_SECONDS:-2}"

  for attempt in $(seq 1 "$attempts"); do
    if run_artisan "$@"; then
      return 0
    fi

    if [ "$attempt" -eq "$attempts" ]; then
      return 1
    fi

    sleep "$delay"
  done
}

wait_for_service_replicas() {
  local service="$1"
  local attempts="${SERVICE_READY_ATTEMPTS:-60}"
  local delay="${SERVICE_READY_DELAY_SECONDS:-2}"
  local replicas
  local running
  local desired

  for _ in $(seq 1 "$attempts"); do
    replicas="$(docker service ls --filter "name=${STACK_NAME}_${service}" --format '{{.Replicas}}')"
    running="${replicas%%/*}"
    desired="${replicas##*/}"

    if [ -n "$replicas" ] && [ "$running" = "$desired" ] && [ "$desired" != "0" ]; then
      return 0
    fi

    sleep "$delay"
  done

  echo "Timed out waiting for ${STACK_NAME}_${service} replicas to become ready." >&2
  docker service ps "${STACK_NAME}_${service}" || true
  return 1
}

if docker network inspect "$NETWORK_NAME" >/dev/null 2>&1; then
  run_artisan_with_retry migrate --force
  MIGRATED_BEFORE_DEPLOY=1
fi

docker stack deploy --with-registry-auth --compose-file "$STACK_FILE" "$STACK_NAME"

wait_for_service_replicas mysql
wait_for_service_replicas redis
wait_for_service_replicas app
wait_for_service_replicas nginx

if [ "$MIGRATED_BEFORE_DEPLOY" -eq 0 ]; then
  run_artisan_with_retry migrate --force
fi

run_artisan queue:restart

run_artisan schedule:interrupt || true

wait_for_service_replicas queue
wait_for_service_replicas scheduler

docker service ls --filter "name=${STACK_NAME}_"
