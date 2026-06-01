# Контекст проекта для Gemini Notebook
## ИС «Quattro» - веб-платформа домашней мебели с онлайн-конфигуратором

> **Как использовать:** загрузи этот файл в Notebook Gemini (или вставь целиком в «Источники» / системную инструкцию блокнота). При каждом новом чате в блокноте Gemini уже будет знать проект - тогда проси «напиши промпт для Cursor» или «сформулируй задачу для агента».

---

## 1. Суть проекта (одним абзацем)

**Дипломный full-stack на PHP 8.2 + MySQL без фреймворков.** Бренд интерфейса: **Quattro** - домашняя мебель (спальня, гостиная, кухня, домашний офис, прихожая, детская). Раньше в схеме была «диспетчерская мебель ETEO»; контент и миграции **004+** перевели каталог на домашнюю мебель, но в коде/БД могут остаться старые SKU (`ETEO-D1-BASE` маппится на `BEDROOM-SET-01` в `views/helpers.php`).

**Главная ценность для защиты:** онлайн-конфигуратор с пересчётом цены + запись заказа в БД через REST API с CSRF + отдельная таблица заявок `leads`. Референс UX когда-то был eteo.ru; сейчас это **не клон**, а упрощённый корпоративный лендинг + каталог + конфигуратор.

**Репозиторий:** `furniture_platform` (локально часто `~/Downloads/furniture_platform`).

---

## 2. Стек и ограничения

| Слой | Технологии |
|------|------------|
| Backend | PHP 8.2+, strict_types, namespace `App\`, без Composer/Laravel |
| БД | MySQL 8, InnoDB, PDO (Singleton `App\Config\Database`) |
| Frontend | Vanilla JS (`public/js/site.js`, `configurator.js`), CSS (`site.css`, `configurator.css`) |
| Шаблоны | PHP views + `render()` + layout `views/layouts/main.php` |
| Сервер dev | `php -S localhost:8080 router.php` через `./scripts/dev.sh` |
| Prod-идея | Apache + `.htaccess` → `index.php` |

**Не делать без явной просьбы:** админка, Laravel/Symfony, React, мультиязычный UI (RU/KK в БД есть, UI - русский), email-рассылки, Docker как обязательность, полный клон eteo.ru.

**Язык ответов пользователю:** русский.

---

## 3. Архитектура (кратко)

```
Запрос → router.php (php -S) или .htaccess → index.php (Front Controller)
       → маршрутизация по сегментам URL
       → Controller (Product / Order / Lead)
       → PDO / MySQL
       → render('view', $data) → views/*.php в layout
```

**Автозагрузка:** `App\Controllers\OrderController` → `controllers/OrderController.php`.

**Конфиг:** `config/config.php` (env: `DB_*`, `APP_DEBUG`, `SESSION_SECURE`).

**Безопасность:** HTTP-заголовки в `index.php`, CSRF в сессии (`OrderController` / `LeadController`), prepared statements, `htmlspecialchars` при санитизации, цены заказа **только из БД**, не с клиента.

---

## 4. Маршруты (актуальные)

| Метод | URL | Что делает |
|-------|-----|------------|
| GET | `/` | Главная: hero, направления, конфигуратор-карусель, кейсы, lead-form, FAQ |
| GET | `/catalog` | Хаб категорий из MySQL |
| GET | `/catalog/{id}` | Товары категории (id числовой) |
| GET | `/product/{id}` или `/product/{slug}` | Карточка + конфигуратор |
| GET | `/cart` | Страница корзины (заглушка/минимум) |
| GET | `/privacy` | Политика конфиденциальности |
| GET | `/order/success/{id}` | Подтверждение заказа |
| GET | `/api/product/{id}` | JSON товара с опциями |
| GET | `/api/csrf-token` | JSON `{ "token": "..." }` |
| POST | `/api/order/create` | Создание заказа (JSON + CSRF + cookies) |
| POST | `/api/lead/create` | Заявка на КП |

Локаль: `?lang=ru|kk` → `$_SESSION['locale']` (контроллеры читают суффикс `_ru` / `_kk` в SQL).

---

## 5. Ключевые файлы (куда лезть)

| Задача | Файлы |
|--------|--------|
| Новый маршрут | `index.php` |
| Каталог / товары / API товара | `controllers/ProductController.php` |
| Заказы | `controllers/OrderController.php` |
| Заявки КП | `controllers/LeadController.php` |
| Подключение БД | `config/Database.php`, `config/config.php` |
| Рендер страниц | `includes/view.php`, `views/layouts/main.php` |
| Главная | `views/home.php`, `views/partials/sections/*` |
| Конфигуратор UI | `views/product/card.php`, `public/js/configurator.js`, `public/css/configurator.css` |
| Метаданные конфигуратора по SKU | `views/helpers.php` → `product_configurator_meta()`, `product_sku_key()` |
| Стили сайта | `public/css/site.css` |
| Схема БД | `sql/schema.sql`, `sql/migrations/*.sql`, `./sql/migrate.sh` |
| Запуск | `scripts/dev.sh`, `README.md` |
| QA защиты | `docs/QA-DIPLOMA.md` |
| План диплома | `docs/IMPLEMENTATION-OPTIMUM.md` |
| Листинг для приложения | `docs/листинг-серверная-часть-полный.txt` |

---

## 6. База данных

**База:** `furniture_platform`, utf8mb4.

**Основные таблицы:**
- `categories` - slug: bedroom, living-room, kitchen, home-office, entryway, kids
- `products` - base_price, image_preview, sku, slug, active, category_id, series_id
- `product_options` - option_type: select|checkbox, option_group: material|config|extras, price_modifier
- `orders`, `order_items` - заказ; options снимаются в JSON
- `leads` - заявки КП (source: home|cases|contacts|product)
- `pages`, `cases`, `faq_items`, `site_settings`, `product_series` - контент (частично статика в PHP)

**Развёртывание:** `schema.sql` → все файлы в `sql/migrations/` по порядку (`dev.sh` применяет автоматически).

**Типичные SKU (после миграций домашней мебели):**
`BEDROOM-SET-01`, `BEDROOM-BED-02`, `LIVING-ROOM-SET-01`, `LIVING-CHAIR-02`, `KITCHEN-DINING-01`, `HOME-OFFICE-01`, `ENTRYWAY-01`, `KIDS-ROOM-01`.

---

## 7. Конфигуратор (бизнес-логика)

1. Опции товара в `product_options`; группы `material`, `config`, `extras`.
2. **select** - одно значение в группе; **checkbox** - несколько.
3. Цена на клиенте: `base_price + Σ price_modifier` (проверка при заказе - снова из БД в `OrderController::persistOrder`).
4. Тексты групп для UI - в `product_configurator_meta($sku)` в `views/helpers.php`.
5. На главной - карусель/сетка моделей с опциями: `filter_configurable_products()`, partials `configurator-carousel.php`, `configurator-models-grid.php`.

---

## 8. Что уже сделано / текущая фаза

- Единый дизайн `site.css` (убраны отдельные темы site-warm/luxury/…).
- Категории домашней мебели + изображения в `public/images/categories/`, `public/images/products/`.
- Миграции 005-009: картинки, цены, упрощение кресла, переименования.
- Slug-URL товаров (`/product/bedroom-set-1`).
- Страница privacy, блок конфигуратора на главной.
- Документы для диплома: листинг серверной части + SQL.

**Документация может отставать:** `QA-DIPLOMA.md` и `IMPLEMENTATION-OPTIMUM.md` ещё упоминают ETEO/диспетчерскую - **источник правды: код и миграции 004+**, UI Quattro.

---

## 9. Scope диплома (не раздувать)

**В scope:** лендинг, каталог, конфигуратор, заказ, лиды, CSRF, транзакции, 404/500 с layout, политика privacy.

**Вне scope (если не попросили явно):** админка, RU/KK переключатель, отдельные URL кейсов/услуг, email, sitemap, корзина как полноценный модуль, Docker обязателен.

---

## 10. Команды разработчика

```bash
# macOS/Linux
./scripts/dev.sh
# → http://localhost:8080

mysql -u root furniture_platform -e "SHOW TABLES"
curl -s http://localhost:8080/api/csrf-token
curl -s http://localhost:8080/api/product/1
```

Windows: `start-dev.ps1` / `start-dev.cmd`, см. `README.md`.

---

## 11. Как Gemini должен писать «норм промпты» для Cursor / другого ИИ

Когда пользователь просит промпт - выдавай **готовый блок на русском**, структура:

```
Контекст: [1-2 предложения - что за проект Quattro, PHP без фреймворка]
Цель: [конкретный измеримый результат]
Файлы: [точные пути, которые трогать]
Ограничения: [минимальный diff, не трогать X, стиль как в соседних файлах, без новых зависимостей]
Проверка: [URL или curl / что увидеть в браузере]
```

**Примеры хороших формулировок цели:**
- «Добавь в `ProductController` фильтр по slug категории без ломания `/catalog/{id}`»
- «Исправь пересчёт цены в `configurator.js`, когда сняты все checkbox в группе extras»
- «Обнови `docs/QA-DIPLOMA.md` под домашнюю мебель и SKU BEDROOM-SET-01»

**Плохие промпты (не предлагать):**
- «Сделай сайт лучше»
- «Перепиши на Laravel»
- «Добавь всё из eteo.ru»

**Для правок UI:** указывать `public/css/site.css` или `configurator.css`, классы BEM-подобные уже в проекте (`site-header`, `home-hero`, `configurator`).

**Для правок API:** напоминать про CSRF, cookies, формат JSON из `OrderController::createOrder`.

**Для БД:** новые поля - отдельный файл `sql/migrations/0XX_название.sql`, идемпотентно где возможно.

---

## 12. Шаблоны промптов (копировать в Cursor)

### Новая секция на главной
```
Проект: Quattro, PHP furniture_platform, views/partials/sections/.
Добавь секцию «[название]» на главную: partial в sections/, подключи в views/home.php после [блок].
Стиль как hero/advantages в site.css. Данные пока статические на русском. Минимальный diff.
Проверка: http://localhost:8080/#якорь
```

### Баг конфигуратора
```
Проект: Quattro, public/js/configurator.js + views/product/card.php.
Баг: [описание]. Воспроизведение: /product/[slug], шаги 1-2-3.
Исправь без изменения API. Цены только из data-атрибутов/ответа API.
Проверка: смена опций меняет итог, заказ уходит на /api/order/create.
```

### Миграция БД
```
Проект: furniture_platform, MySQL.
Нужно: [описание поля/таблицы].
Создай sql/migrations/0XX_*.sql (идемпотентно), обнови ProductController если нужно SELECT.
Не меняй schema.sql задним числом без необходимости. Проверка: ./scripts/dev.sh и SELECT в БД.
```

### Диплом / текст
```
Проект: диплом Quattro, ИС домашней мебели, PHP+MySQL+конфигуратор.
Напиши [абзац/подраздел] для пояснительной записки: [тема].
Опирайся на: Front Controller index.php, PDO, CSRF, таблицы orders/leads/product_options.
Без выдуманных технологий. Стиль академический, русский.
```

---

## 13. Таблица «кто за что» при параллельной работе

| Тема | Не путать |
|------|-----------|
| Бренд UI | Quattro |
| Старый код/демо | ETEO, диспетчерская (legacy в schema.sql seed) |
| Реальный каталог | миграция 004 + 005-009 |
| Сервер | `controllers/`, `index.php` |
| Клиент | `public/js/`, `public/css/` |
| Шаблоны | `views/` (не путать с API-логикой) |

---

## 14. Частые вопросы пользователя

- **Листинг для диплома** → `docs/листинг-серверная-часть-полный.txt` (PHP + SQL schema 001-002).
- **Сценарий защиты** → `docs/QA-DIPLOMA.md` (обновить под Quattro при расхождении).
- **Почему нет Composer** → осознанно, дипломная простота.
- **Корзина** → маршрут есть, полноценная логика не в фокусе диплома; заказ из конфигуратора.

---

## 15. Версия контекста

- Обновлено: 2026-06-02
- Ветка/состояние: активная разработка UI домашней мебели, миграции до 009, единый `site.css`.

При изменении архитектуры или бренда - **обнови этот файл** и перезагрузи в Gemini Notebook.
