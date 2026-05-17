#!/usr/bin/env bash
set -euo pipefail

STACK_NAME="${STACK_NAME:-collabbing}"
STACK_FILE="${STACK_FILE:-docker/production/stack.yaml}"
APP_ENV_FILE="${APP_ENV_FILE:-.env.production}"
DOCKER_ENV_FILE="${DOCKER_ENV_FILE:-docker/production/${APP_ENV_FILE}}"
IMAGE="${IMAGE:?Set IMAGE to the production image tag to deploy.}"
NETWORK_NAME="${STACK_NAME}_app_network"
MIGRATED_BEFORE_DEPLOY=0

export APP_ENV_FILE IMAGE

if [ ! -f "$DOCKER_ENV_FILE" ]; then
  echo "Missing production environment file: $DOCKER_ENV_FILE" >&2
  exit 1
fi

set -a
# shellcheck source=/dev/null
. "$DOCKER_ENV_FILE"
set +a

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

if docker network inspect "$NETWORK_NAME" >/dev/null 2>&1; then
  run_artisan migrate --force
  MIGRATED_BEFORE_DEPLOY=1
fi

docker stack deploy --with-registry-auth --compose-file "$STACK_FILE" "$STACK_NAME"

for _ in $(seq 1 60); do
  if docker service ls --filter "name=${STACK_NAME}_app" --format '{{.Replicas}}' | grep -Eq '^[1-9][0-9]*/[1-9][0-9]*$'; then
    break
  fi

  sleep 2
done

if [ "$MIGRATED_BEFORE_DEPLOY" -eq 0 ]; then
  run_artisan migrate --force
fi

run_artisan queue:restart

run_artisan schedule:interrupt || true

docker service ls --filter "name=${STACK_NAME}_"
