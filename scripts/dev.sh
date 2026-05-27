#!/usr/bin/env bash
# Запуск локального dev-сервера (macOS / Linux / Git Bash)
# Из корня: ./scripts/dev.sh  или  ./start-dev.sh

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if [[ ! -f "$ROOT/router.php" ]]; then
  echo "ОШИБКА: запусти из корня проекта (где router.php)."
  exit 1
fi

# PHP
PHP="${PHP:-}"
if [[ -z "$PHP" ]]; then
  if command -v php >/dev/null 2>&1; then
    PHP="$(command -v php)"
  elif [[ -x /opt/homebrew/bin/php ]]; then
    PHP=/opt/homebrew/bin/php
  else
    echo "ОШИБКА: PHP не найден. macOS: brew install php"
    exit 1
  fi
fi

if ! "$PHP" -r 'exit version_compare(PHP_VERSION, "8.2.0", ">=") ? 0 : 1);' 2>/dev/null; then
  echo "ОШИБКА: нужен PHP 8.2+"
  "$PHP" -v || true
  exit 1
fi

if ! "$PHP" -m 2>/dev/null | grep -q '^pdo_mysql$'; then
  echo "ОШИБКА: расширение pdo_mysql не загружено."
  echo "Открой php.ini (php --ini) и включи extension=pdo_mysql"
  exit 1
fi

echo "PHP: $PHP ($("$PHP" -v | head -1))"

# MySQL
MYSQL="${MYSQL:-}"
if [[ -z "$MYSQL" ]]; then
  if command -v mysql >/dev/null 2>&1; then
    MYSQL="$(command -v mysql)"
  elif [[ -x /opt/homebrew/bin/mysql ]]; then
    MYSQL=/opt/homebrew/bin/mysql
  else
    echo "ОШИБКА: mysql не найден. macOS: brew install mysql"
    exit 1
  fi
fi

export APP_DEBUG="${APP_DEBUG:-true}"
export APP_URL="${APP_URL:-http://localhost:8080}"
export SESSION_SECURE="${SESSION_SECURE:-false}"
export DB_HOST="${DB_HOST:-127.0.0.1}"
export DB_PORT="${DB_PORT:-3306}"
export DB_NAME="${DB_NAME:-furniture_platform}"
export DB_USER="${DB_USER:-root}"
export DB_PASSWORD="${DB_PASSWORD:-}"

MYSQL_ARGS=(-u "$DB_USER")
[[ -n "$DB_PASSWORD" ]] && MYSQL_ARGS+=(-p"$DB_PASSWORD")

echo "MySQL: $MYSQL"

if ! "$MYSQL" "${MYSQL_ARGS[@]}" -e "SELECT 1" >/dev/null 2>&1; then
  echo "MySQL не отвечает, пробую запустить..."
  if command -v brew >/dev/null 2>&1; then
    brew services start mysql >/dev/null 2>&1 || true
    sleep 3
  fi
fi

if ! "$MYSQL" "${MYSQL_ARGS[@]}" -e "SELECT 1" >/dev/null 2>&1; then
  echo "ОШИБКА: не удалось подключиться к MySQL."
  exit 1
fi

if ! "$MYSQL" "${MYSQL_ARGS[@]}" -e "USE $DB_NAME" >/dev/null 2>&1; then
  echo "Создание базы из sql/schema.sql..."
  "$MYSQL" "${MYSQL_ARGS[@]}" < "$ROOT/sql/schema.sql"
fi

echo ""
echo "========================================"
echo "Сайт:         http://localhost:8080"
echo "Конфигуратор: http://localhost:8080/product/1"
echo "Остановка:    Ctrl+C"
echo "========================================"
echo ""

exec "$PHP" -S localhost:8080 "$ROOT/router.php"
