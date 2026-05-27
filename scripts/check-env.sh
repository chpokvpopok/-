#!/usr/bin/env bash
# Диагностика окружения (macOS / Linux / Git Bash на Windows)
# Запуск из корня: ./scripts/check-env.sh

set -uo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
# shellcheck source=lib/find-tools.sh
source "$ROOT/scripts/lib/find-tools.sh"
cd "$ROOT"

echo "=== Проверка окружения (bash) ==="
echo "Shell: $SHELL ($(uname -s 2>/dev/null || echo unknown))"
echo "PWD:   $ROOT"
echo ""

# --- PHP ---
echo "--- PHP ---"
PHP_BIN=""
if PHP_BIN="$(find_php_bin 2>/dev/null)"; then
  echo "  $PHP_BIN"
  "$PHP_BIN" -v | head -1
  echo ""
  echo "php --ini:"
  "$PHP_BIN" --ini
  echo ""
  echo "Модули (pdo / mysql):"
  "$PHP_BIN" -m 2>/dev/null | grep -iE 'pdo|mysql' || true
  if "$PHP_BIN" -m 2>/dev/null | grep -q '^pdo_mysql$'; then
    echo "pdo_mysql: OK"
  else
    echo "pdo_mysql: НЕТ — включи extension=pdo_mysql в php.ini"
  fi
  echo ""
  echo "Для запуска (скопируй в этот терминал):"
  echo "  export PHP=\"$PHP_BIN\""
else
  echo "  php не найден (ни в PATH, ни в типичных папках Windows)"
  echo "  Git Bash: установи PHP (winget install PHP.PHP) или XAMPP"
  echo "  macOS:    brew install php"
  if _is_windows_bash; then
    echo ""
    echo "  Либо укажи вручную:"
    echo '  export PHP="/c/Program Files/.../php.exe"'
  fi
fi

echo ""

# --- MySQL ---
echo "--- MySQL ---"
MYSQL_BIN=""
if MYSQL_BIN="$(find_mysql_bin 2>/dev/null)"; then
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
      echo "База furniture_platform: нет (нужен: mysql ... < sql/schema.sql)"
    fi
  else
    echo "Подключение: ОШИБКА — запусти службу MySQL (services.msc)"
  fi
  echo ""
  echo "Для запуска:"
  echo "  export MYSQL=\"$MYSQL_BIN\""
else
  echo "  mysql не найден"
  echo "  Git Bash: MySQL 8.4 обычно здесь:"
  echo '    export MYSQL="/c/Program Files/MySQL/MySQL Server 8.4/bin/mysql.exe"'
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
if [[ -n "${PHP_BIN:-}" && -n "${MYSQL_BIN:-}" ]]; then
  echo "Дальше:"
  echo "  export PHP=\"$PHP_BIN\""
  echo "  export MYSQL=\"$MYSQL_BIN\""
  echo "  ./start-dev.sh"
else
  echo "Дальше: найди php/mysql, export PHP=... MYSQL=..., затем ./start-dev.sh"
  echo "На Windows проще: start-dev.cmd (из CMD) или .\\start-dev.ps1 (PowerShell)"
fi
