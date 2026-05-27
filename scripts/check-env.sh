#!/usr/bin/env bash
# Диагностика окружения (macOS / Linux / Git Bash)
# Запуск из корня: ./scripts/check-env.sh

set -uo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "=== Проверка окружения (bash) ==="
echo "Shell: $SHELL"
echo "PWD:   $ROOT"
echo ""

# --- PHP ---
echo "--- PHP ---"
if command -v php >/dev/null 2>&1; then
  PHP_BIN="$(command -v php)"
  echo "  $PHP_BIN"
  php -v | head -1
  echo ""
  echo "php --ini:"
  php --ini
  echo ""
  echo "Модули (pdo / mysql):"
  php -m 2>/dev/null | grep -iE 'pdo|mysql' || true
  if php -m 2>/dev/null | grep -q '^pdo_mysql$'; then
    echo "pdo_mysql: OK"
  else
    echo "pdo_mysql: НЕТ — включи extension=pdo_mysql в php.ini"
  fi
else
  echo "  php не в PATH"
  echo "  macOS: brew install php"
fi

echo ""

# --- MySQL ---
echo "--- MySQL ---"
MYSQL_BIN="${MYSQL:-}"
if [[ -z "$MYSQL_BIN" ]]; then
  if command -v mysql >/dev/null 2>&1; then
    MYSQL_BIN="$(command -v mysql)"
  elif [[ -x /opt/homebrew/bin/mysql ]]; then
    MYSQL_BIN=/opt/homebrew/bin/mysql
  elif [[ -x /usr/local/mysql/bin/mysql ]]; then
    MYSQL_BIN=/usr/local/mysql/bin/mysql
  fi
fi

if [[ -n "$MYSQL_BIN" ]]; then
  echo "  $MYSQL_BIN"
  "$MYSQL_BIN" --version 2>/dev/null || true
  echo ""
  if "$MYSQL_BIN" -u "${DB_USER:-root}" ${DB_PASSWORD:+-p"$DB_PASSWORD"} -e "SELECT 1" 2>/dev/null; then
    echo "Подключение: OK"
    if "$MYSQL_BIN" -u "${DB_USER:-root}" ${DB_PASSWORD:+-p"$DB_PASSWORD"} -e "USE furniture_platform" 2>/dev/null; then
      echo "База furniture_platform: OK"
      COUNT="$("$MYSQL_BIN" -u "${DB_USER:-root}" ${DB_PASSWORD:+-p"$DB_PASSWORD"} -N -e "SELECT COUNT(*) FROM furniture_platform.products" 2>/dev/null || echo "?")"
      echo "Товаров в products: $COUNT"
    else
      echo "База furniture_platform: нет (нужен sql/schema.sql)"
    fi
  else
    echo "Подключение: ОШИБКА (запусти MySQL)"
    if command -v brew >/dev/null 2>&1; then
      echo "  macOS: brew services start mysql"
    fi
  fi
else
  echo "  mysql не в PATH"
  echo "  macOS: brew install mysql"
fi

echo ""

# --- Проект ---
echo "--- Проект ---"
for f in router.php index.php sql/schema.sql; do
  if [[ -f "$ROOT/$f" ]]; then
    echo "  OK  $f"
  else
    echo "  НЕТ $f"
  fi
done

echo ""
echo "Дальше: ./scripts/dev.sh   или   ./start-dev.sh"
