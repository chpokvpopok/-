#!/usr/bin/env bash
# Запуск локального dev-сервера (macOS / Linux / Git Bash)
# Из корня: ./scripts/dev.sh  или  ./start-dev.sh

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
# shellcheck source=lib/find-tools.sh
source "$ROOT/scripts/lib/find-tools.sh"
cd "$ROOT"

if [[ ! -f "$ROOT/router.php" ]]; then
  echo "ОШИБКА: запусти из корня проекта (где router.php)."
  exit 1
fi

PHP="$(find_php_bin)" || {
  echo "ОШИБКА: PHP не найден."
  echo "  ./scripts/check-env.sh"
  echo '  export PHP="/c/.../php.exe"   # Git Bash на Windows'
  exit 1
}

if ! "$PHP" -r 'exit(PHP_VERSION_ID >= 80200 ? 0 : 1);' 2>/dev/null; then
  echo "ОШИБКА: нужен PHP 8.2+"
  "$PHP" -v || true
  exit 1
fi

if ! "$PHP" -r 'exit(extension_loaded("pdo_mysql") ? 0 : 1);' 2>/dev/null; then
  echo "ОШИБКА: расширение pdo_mysql не загружено."
  echo "Открой php.ini ($("$PHP" --ini)) и включи extension=pdo_mysql"
  exit 1
fi

echo "PHP: $PHP ($("$PHP" -v | head -1))"

MYSQL="$(find_mysql_bin)" || {
  echo "ОШИБКА: mysql не найден."
  echo '  export MYSQL="/c/Program Files/MySQL/MySQL Server 8.4/bin/mysql.exe"'
  exit 1
}

export APP_DEBUG="${APP_DEBUG:-true}"
export APP_URL="${APP_URL:-http://localhost:8080}"
export SESSION_SECURE="${SESSION_SECURE:-false}"
export DB_HOST="${DB_HOST:-127.0.0.1}"
export DB_PORT="${DB_PORT:-3306}"
export DB_NAME="${DB_NAME:-furniture_platform}"
export DB_USER="${DB_USER:-root}"
export DB_PASSWORD="${DB_PASSWORD:-}"

MYSQL_ARGS=(-u "$DB_USER" --default-character-set=utf8mb4)
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
  echo "ОШИБКА: не удалось подключиться к MySQL (служба выключена?)"
  exit 1
fi

if ! "$MYSQL" "${MYSQL_ARGS[@]}" -e "USE $DB_NAME" >/dev/null 2>&1; then
  echo "Создание базы из sql/schema.sql..."
  "$MYSQL" "${MYSQL_ARGS[@]}" < "$ROOT/sql/schema.sql"
fi

if [[ -d "$ROOT/sql/migrations" ]]; then
  echo "Применение миграций..."
  for f in "$ROOT"/sql/migrations/*.sql; do
    [[ -f "$f" ]] || continue
    echo "  $(basename "$f")"
    "$MYSQL" "${MYSQL_ARGS[@]}" "$DB_NAME" < "$f" || {
      echo "ОШИБКА: миграция $(basename "$f") не применилась."
      exit 1
    }
  done
  echo "Миграции применены."
fi

echo ""
echo "========================================"
echo "Сайт:         http://localhost:8080"
echo "Конфигуратор: http://localhost:8080/product/1"
echo "Остановка:    Ctrl+C"
echo "========================================"
echo ""

exec "$PHP" -S localhost:8080 "$ROOT/router.php"
