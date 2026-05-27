# Общий поиск php/mysql: macOS, Linux, Git Bash (Windows)
# shellcheck shell=bash

_is_windows_bash() {
  case "$(uname -s 2>/dev/null)" in
    MINGW*|MSYS*|CYGWIN*) return 0 ;;
  esac
  [[ -d /c/Windows ]] && return 0
  return 1
}

_win_path() {
  # "C:\Program Files\..." -> /c/Program Files/...
  local p="$1"
  if [[ "$p" =~ ^[A-Za-z]: ]]; then
    local drive="${p:0:1}"
    p="/${drive,,}${p:2}"
    p="${p//\\//}"
  fi
  printf '%s' "$p"
}

_find_php_windows() {
  local candidates=() dir exe

  [[ -x /c/xampp/php/php.exe ]] && candidates+=("/c/xampp/php/php.exe")
  [[ -x /c/php/php.exe ]] && candidates+=("/c/php/php.exe")
  [[ -x "/c/Program Files/PHP/php.exe" ]] && candidates+=("/c/Program Files/PHP/php.exe")

  local winget_base="${LOCALAPPDATA:-}"
  [[ -z "$winget_base" && -n "${USERPROFILE:-}" ]] && winget_base="${USERPROFILE}/AppData/Local"
  [[ -z "$winget_base" && -d "/c/Users" ]] && {
  for dir in /c/Users/*/AppData/Local; do
    [[ -d "$dir" ]] && winget_base="$dir" && break
  done
  }

  if [[ -n "$winget_base" ]]; then
    winget_base="$(_win_path "$winget_base")"
    local pkg="${winget_base}/Microsoft/WinGet/Packages"
    if [[ -d "$pkg" ]]; then
      while IFS= read -r exe; do
        candidates+=("$exe")
      done < <(find "$pkg" -name 'php.exe' 2>/dev/null | head -20)
    fi
  fi

  local best="" best_ver="0" ver
  for exe in "${candidates[@]}"; do
    [[ -x "$exe" ]] || continue
    "$exe" -r 'exit version_compare(PHP_VERSION,"8.2.0",">=")?0:1;' 2>/dev/null || continue
    ver="$("$exe" -r 2>/dev/null || echo 0)"
    if [[ "$(printf '%s\n%s\n' "$best_ver" "$ver" | sort -V | tail -1)" == "$ver" ]]; then
      best="$exe"
      best_ver="$ver"
    fi
  done
  [[ -n "$best" ]] && printf '%s' "$best"
}

_find_mysql_windows() {
  local candidates=() dir exe

  [[ -x /c/xampp/mysql/bin/mysql.exe ]] && candidates+=("/c/xampp/mysql/bin/mysql.exe")

  local base="/c/Program Files/MySQL"
  if [[ -d "$base" ]]; then
    for dir in "$base"/MySQL\ Server\ *; do
      exe="$dir/bin/mysql.exe"
      [[ -x "$exe" ]] && candidates+=("$exe")
    done
  fi

  local best=""
  for exe in "${candidates[@]}"; do
    [[ -x "$exe" ]] && best="$exe"
  done
  # предпочитаем более новую версию (последняя в sort)
  if [[ ${#candidates[@]} -gt 0 ]]; then
    best="$(printf '%s\n' "${candidates[@]}" | sort -V | tail -1)"
  fi
  [[ -n "$best" ]] && printf '%s' "$best"
}

find_php_bin() {
  local php="${PHP:-}"
  [[ -n "$php" && -x "$php" ]] && { printf '%s' "$php"; return 0; }

  if command -v php >/dev/null 2>&1; then
    command -v php
    return 0
  fi

  [[ -x /opt/homebrew/bin/php ]] && { printf '%s' /opt/homebrew/bin/php; return 0; }
  [[ -x /usr/local/bin/php ]] && { printf '%s' /usr/local/bin/php; return 0; }

  if _is_windows_bash; then
    local w
    w="$(_find_php_windows)"
    [[ -n "$w" ]] && { printf '%s' "$w"; return 0; }
  fi

  return 1
}

find_mysql_bin() {
  local mysql="${MYSQL:-}"
  [[ -n "$mysql" && -x "$mysql" ]] && { printf '%s' "$mysql"; return 0; }

  if command -v mysql >/dev/null 2>&1; then
    command -v mysql
    return 0
  fi

  [[ -x /opt/homebrew/bin/mysql ]] && { printf '%s' /opt/homebrew/bin/mysql; return 0; }
  [[ -x /usr/local/mysql/bin/mysql ]] && { printf '%s' /usr/local/mysql/bin/mysql; return 0; }

  if _is_windows_bash; then
    local w
    w="$(_find_mysql_windows)"
    [[ -n "$w" ]] && { printf '%s' "$w"; return 0; }
  fi

  return 1
}
