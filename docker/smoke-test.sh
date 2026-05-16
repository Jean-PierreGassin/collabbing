#!/usr/bin/env bash
set -euo pipefail

KIT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TMP_DIR="$(mktemp -d)"
APP_A_DIR="$TMP_DIR/app-a"
APP_B_DIR="$TMP_DIR/app-b"
ENV_A_FILE="$TMP_DIR/.env.a"
ENV_B_FILE="$TMP_DIR/.env.b"

cleanup() {
  for env_file in "$ENV_A_FILE" "$ENV_B_FILE"; do
    if [[ -f "$env_file" ]]; then
      docker compose --env-file "$env_file" -f "$KIT_DIR/compose.yaml" down -v --remove-orphans >/dev/null 2>&1 || true
    fi
  done
  rm -rf "$TMP_DIR"
}

trap cleanup EXIT

create_app() {
  local app_dir="$1"
  local response="$2"

  mkdir -p "$app_dir/public"

  cat >"$app_dir/public/index.php" <<PHP
<?php
header('Content-Type: text/plain');
echo '$response';
PHP
}

write_env() {
  local env_file="$1"
  local app_name="$2"
  local app_dir="$3"
  local base_port="$4"

  cp "$KIT_DIR/.env.example" "$env_file"

  {
    echo
    echo "APP_NAME=$app_name"
    echo "APP_HOST_PATH=$app_dir"
    echo "APP_HTTP_PORT=$base_port"
    echo "MAILHOG_SMTP_PORT=$((base_port + 1))"
    echo "MAILHOG_HTTP_PORT=$((base_port + 2))"
    echo "MYSQL_PORT=$((base_port + 3))"
    echo "REDIS_PORT=$((base_port + 4))"
  } >>"$env_file"
}

compose() {
  local env_file="$1"
  shift

  docker compose --env-file "$env_file" -f "$KIT_DIR/compose.yaml" "$@"
}

wait_for_mysql() {
  local env_file="$1"

  for _ in $(seq 1 30); do
    if compose "$env_file" exec -T mysql mysqladmin ping -h127.0.0.1 -uroot -proot >/dev/null 2>&1; then
      return 0
    fi
    sleep 2
  done

  compose "$env_file" logs --no-color mysql >&2 || true
  echo "MySQL did not become ready during smoke test." >&2
  return 1
}

assert_http_response() {
  local port="$1"
  local expected_response="$2"

  local response
  response="$(curl -fsS "http://127.0.0.1:$port/")"

  if [[ "$response" != "$expected_response" ]]; then
    echo "Unexpected HTTP response on port $port: $response" >&2
    exit 1
  fi
}

RUN_ID="$(date +%s)$RANDOM"
APP_A_NAME="smokea${RUN_ID}"
APP_B_NAME="smokeb${RUN_ID}"
BASE_PORT="$((20000 + RANDOM % 9000))"
APP_A_PORT="$BASE_PORT"
APP_B_PORT="$((BASE_PORT + 10))"

create_app "$APP_A_DIR" "smoke-test-a-ok"
create_app "$APP_B_DIR" "smoke-test-b-ok"
write_env "$ENV_A_FILE" "$APP_A_NAME" "$APP_A_DIR" "$APP_A_PORT"
write_env "$ENV_B_FILE" "$APP_B_NAME" "$APP_B_DIR" "$APP_B_PORT"

compose "$ENV_A_FILE" up -d --build
compose "$ENV_B_FILE" up -d --build

wait_for_mysql "$ENV_A_FILE"
wait_for_mysql "$ENV_B_FILE"

compose "$ENV_A_FILE" ps -a
compose "$ENV_B_FILE" ps -a
compose "$ENV_A_FILE" exec -T node sh -lc 'node -v && yarn -v'
compose "$ENV_A_FILE" exec -T php sh -lc 'php -v && composer --version'
compose "$ENV_A_FILE" exec -T mysql mysqladmin ping -h127.0.0.1 -uroot -proot
compose "$ENV_A_FILE" exec -T redis redis-cli ping
compose "$ENV_B_FILE" exec -T mysql mysqladmin ping -h127.0.0.1 -uroot -proot
compose "$ENV_B_FILE" exec -T redis redis-cli ping

assert_http_response "$APP_A_PORT" "smoke-test-a-ok"
assert_http_response "$APP_B_PORT" "smoke-test-b-ok"
