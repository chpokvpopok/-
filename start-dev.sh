#!/usr/bin/env bash
# Запуск dev-сервера из корня репозитория
exec "$(cd "$(dirname "$0")" && pwd)/scripts/dev.sh" "$@"
