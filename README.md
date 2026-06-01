# Furniture Platform - дипломная версия

Веб-платформа мебельного производства с **онлайн-конфигуратором**, оформлением заказа в MySQL и формой заявки на КП.

- **План задач:** [docs/IMPLEMENTATION-OPTIMUM.md](docs/IMPLEMENTATION-OPTIMUM.md)
- **Гайд для защиты:** [docs/GUIDE-MENYON.md](docs/GUIDE-MENYON.md)
- **QA-чеклист:** [docs/QA-DIPLOMA.md](docs/QA-DIPLOMA.md)

## Требования

| Компонент | Версия |
| --------- | ------ |
| PHP | 8.2+ с расширением `pdo_mysql` |
| MySQL | 8.x |
| Браузер | любой современный |

Демо-URL после запуска:

- Главная: http://localhost:8080/
- Каталог: http://localhost:8080/catalog
- Конфигуратор: http://localhost:8080/product/1

## Запуск на Windows

### 1. Установка

| Программа | Как установить |
| --------- | -------------- |
| **PHP 8.2+** | [windows.php.net](https://windows.php.net/download/) или `winget install PHP.PHP` |
| **MySQL 8** | [MySQL Installer](https://dev.mysql.com/downloads/installer/) или XAMPP (только MySQL) |

Добавь в PATH папки с `php.exe` и `mysql.exe` (часто `C:\Program Files\MySQL\MySQL Server 8.4\bin`).

В `php.ini` включи:

```ini
extension=pdo_mysql
extension=mysqli
```

Проверка:

```powershell
php -v
php -m | findstr pdo_mysql
mysql --version
```

### 2. База данных

Из корня проекта (PowerShell или CMD):

```powershell
# Пустой пароль root (учебная установка). Обязательно utf8mb4, иначе кириллица станет «???????»:
mysql -u root --default-character-set=utf8mb4 < sql\schema.sql

# Если у root есть пароль:
$env:DB_PASSWORD = "ваш_пароль"
mysql -u root -p$env:DB_PASSWORD --default-character-set=utf8mb4 < sql\schema.sql
```

Дополнительные таблицы (лиды, контент):

```powershell
# Git Bash или WSL:
./sql/migrate.sh

# Или вручную (UTF8 в файле + utf8mb4 в клиенте mysql):
Get-Content sql\migrations\001_content_tables.sql -Raw -Encoding UTF8 | mysql -u root --default-character-set=utf8mb4 furniture_platform
Get-Content sql\migrations\002_alter_products.sql -Raw -Encoding UTF8 | mysql -u root --default-character-set=utf8mb4 furniture_platform
Get-Content sql\migrations\003_seed_content.sql -Raw -Encoding UTF8 | mysql -u root --default-character-set=utf8mb4 furniture_platform
```

Если после `git pull` в каталоге вместо «Спальня» / «Домашний офис» видно `???????` - база уже испорчена при импорте. Пересоздайте её:

```powershell
mysql -u root -e "DROP DATABASE IF EXISTS furniture_platform; CREATE DATABASE furniture_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
.\start-dev.ps1
```

Настройки БД по умолчанию в `config/config.php`: host `localhost`, user `root`, пароль пустой, база `furniture_platform`. Переопределение через переменные окружения: `DB_HOST`, `DB_USER`, `DB_PASSWORD`, `DB_NAME`.

### 3. Запуск сервера

**Вариант A - скрипт (рекомендуется):**

```powershell
# Если блокируется ExecutionPolicy - только для текущего окна:
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass
.\start-dev.ps1
```

Или двойной клик / из CMD:

```cmd
start-dev.cmd
```

**Вариант B - вручную (php -S):**

```powershell
cd C:\path\to\furniture_platform
$env:APP_DEBUG = "true"
$env:SESSION_SECURE = "false"
php -S localhost:8080 router.php
```

Открой http://localhost:8080 - должна открыться главная с шапкой и подвалом.

Остановка: `Ctrl+C` в окне терминала.

### 4. Типичные проблемы (Windows)

| Симптом | Решение |
| ------- | ------- |
| `pdo_mysql` не найден | Раскомментируй `extension=pdo_mysql` в `php.ini` (`php --ini`) |
| MySQL не отвечает | `net start MySQL80` или services.msc → запустить MySQL |
| `Unknown database` | Повтори импорт `sql\schema.sql` |
| Вместо русских названий `???????` | Импорт без `utf8mb4` - пересоздай базу (см. раздел «База данных») |
| Скрипты PowerShell заблокированы | `Set-ExecutionPolicy -Scope Process Bypass` или используй `start-dev.cmd` |

## Запуск на macOS / Linux

```bash
brew services start mysql   # если MySQL не запущен
mysql -u root < sql/schema.sql
./sql/migrate.sh            # опционально, контентные таблицы
./scripts/dev.sh
```

## Структура проекта

```
index.php          - маршруты (front controller)
router.php         - точка входа для php -S
controllers/       - ProductController, OrderController, LeadController
views/             - шаблоны страниц и layout
public/css|js/     - site.css (сайт), configurator.css/js (товар)
sql/               - schema.sql и миграции
docs/              - план и QA для диплома
```

## API (для проверки)

```bash
# CSRF
curl -s http://localhost:8080/api/csrf-token

# Данные товара
curl -s http://localhost:8080/api/product/1
```

Полные примеры curl для заказа и лида - в [docs/QA-DIPLOMA.md](docs/QA-DIPLOMA.md).
