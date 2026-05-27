#!/usr/bin/env bash
# =============================================================
# migrate.sh — применение SQL-миграций по порядку (001, 002, 003)
#
# Использование:
#   ./sql/migrate.sh
#   DB_USER=root DB_PASSWORD=secret ./sql/migrate.sh
#
# Переменные окружения (как в config.php / dev.sh):
#   DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD
#   MYSQL — путь к клиенту mysql (по умолчанию: mysql)
# =============================================================

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
MIGRATIONS_DIR="$ROOT/sql/migrations"
MYSQL_BIN="${MYSQL:-mysql}"

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_NAME="${DB_NAME:-furniture_platform}"
DB_USER="${DB_USER:-root}"
DB_PASSWORD="${DB_PASSWORD:-}"

log() { echo "[migrate] $*"; }
err() { echo "[migrate] ERROR: $*" >&2; exit 1; }

if ! command -v "$MYSQL_BIN" >/dev/null 2>&1; then
  err "Клиент mysql не найден. Установите MySQL или задайте MYSQL=/path/to/mysql"
fi

mysql_args=(-h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER")
if [[ -n "$DB_PASSWORD" ]]; then
  mysql_args+=(-p"$DB_PASSWORD")
fi

run_sql() {
  local file="$1"
  log "Применяю: $(basename "$file")"
  if ! "$MYSQL_BIN" "${mysql_args[@]}" < "$file"; then
    err "Ошибка в файле $(basename "$file")"
  fi
}

# База должна существовать (schema.sql из dev.sh или вручную)
if ! "$MYSQL_BIN" "${mysql_args[@]}" -e "USE \`$DB_NAME\`" 2>/dev/null; then
  log "База $DB_NAME не найдена — инициализация из schema.sql"
  if [[ ! -f "$ROOT/sql/schema.sql" ]]; then
    err "Не найден $ROOT/sql/schema.sql"
  fi
  run_sql "$ROOT/sql/schema.sql"
fi

if [[ ! -d "$MIGRATIONS_DIR" ]]; then
  err "Папка миграций не найдена: $MIGRATIONS_DIR"
fi

shopt -s nullglob
files=("$MIGRATIONS_DIR"/*.sql)
shopt -u nullglob

if [[ ${#files[@]} -eq 0 ]]; then
  err "Нет файлов *.sql в $MIGRATIONS_DIR"
fi

IFS=$'\n' sorted=($(printf '%s\n' "${files[@]}" | sort))
unset IFS

for f in "${sorted[@]}"; do
  run_sql "$f"
done

log "Готово. Таблицы в $DB_NAME:"
"$MYSQL_BIN" "${mysql_args[@]}" -e "SHOW TABLES FROM \`$DB_NAME\`"
