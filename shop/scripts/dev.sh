#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PHP="${PHP:-/opt/homebrew/bin/php}"
MYSQL="${MYSQL:-/opt/homebrew/bin/mysql}"

export APP_DEBUG=true
export APP_URL=http://localhost:8080
export SESSION_SECURE=false
export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_NAME=furniture_platform
export DB_USER=root
export DB_PASSWORD=

cd "$ROOT"

if ! command -v "$PHP" >/dev/null 2>&1; then
  echo "PHP не найден. Установите: brew install php"
  exit 1
fi

if ! "$MYSQL" -u root -e "SELECT 1" >/dev/null 2>&1; then
  echo "Запуск MySQL..."
  brew services start mysql >/dev/null 2>&1 || true
  sleep 3
fi

if ! "$MYSQL" -u root -e "USE furniture_platform" >/dev/null 2>&1; then
  echo "Инициализация базы данных..."
  "$MYSQL" -u root < "$ROOT/sql/schema.sql"
fi

echo "Сервер: http://localhost:8080"
exec "$PHP" -S localhost:8080 "$ROOT/router.php"
