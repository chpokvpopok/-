# Пошаговый план реализации (Implementation Roadmap)

> **Базовый документ:** [PLAN-ETEO-SITE.md](./PLAN-ETEO-SITE.md)  
> **Проект:** `furniture_platform`  
> **Назначение:** скармливать этот файл агенту по одной задаче (или пакету) за сессию.  
> **Диплом (укороченный scope):** [IMPLEMENTATION-OPTIMUM.md](./IMPLEMENTATION-OPTIMUM.md) ← использовать для защиты

---

## Как пользоваться этим файлом

### Формат одной сессии с агентом

Скопируй шаблон в конец сообщения:

```text
Реализуй задачу TASK-XXX из docs/IMPLEMENTATION-ROADMAP.md.
Контекст: docs/PLAN-ETEO-SITE.md.
После выполнения: отметь задачу [x], обнови «Текущий статус», перечисли изменённые файлы и команды проверки.
```

### Правила для агента

1. **Одна задача = один логический PR** — не смешивать несвязанные TASK без явного запроса.
2. **Сначала зависимости** — если TASK-N требует TASK-M, убедись что M выполнен.
3. **Не ломать существующее** — `/product/{id}`, `/api/order/create`, CSRF должны работать после каждой задачи.
4. **Проверка** — в конце сессии выполнить «Проверку» из задачи и `./scripts/dev.sh` при необходимости.
5. **Минимальный diff** — только файлы из списка «Файлы» задачи + необходимые правки `index.php`.
6. **Отметка прогресса** — заменить `- [ ]` на `- [x]` для выполненной задачи в этом файле.

### Легенда статусов

| Символ | Значение |
|--------|----------|
| `- [ ]` | Не начато |
| `- [x]` | Выполнено |
| `🔒` | Заблокировано зависимостями |
| `⚡` | Уже есть в проекте (не трогать без нужды) |

---

## Текущий статус проекта

**Обновляй эту секцию после каждой сессии.**

| Поле | Значение |
|------|----------|
| **Последняя выполненная задача** | TASK-009 |
| **Следующая рекомендуемая** | TASK-010 |
| **Сервер** | `./scripts/dev.sh` → http://localhost:8080 |
| **БД** | `furniture_platform` (schema.sql + демо-товары) |

### Уже реализовано (не переделывать)

- [x] Front controller `index.php` + `router.php`
- [x] `ProductController`, `OrderController`
- [x] Конфигуратор `views/product/card.php` + `configurator.js/css`
- [x] API: `GET /api/product/{id}`, `GET /api/csrf-token`, `POST /api/order/create`
- [x] Базовые views: `home.php`, `catalog.php`, `cart.php` (заглушки)
- [x] Автозагрузка lowercase-папок, `getenv()` в config
- [x] Docker Compose, `scripts/dev.sh`

---

## Зафиксированные решения (Фаза 0)

> Агент **не спрашивает повторно**, если пользователь не переопределил.

| # | Решение | Значение |
|---|---------|----------|
| D1 | Референс | UX/структура eteo.ru, **не** копипаст Tilda |
| D2 | Бренд в демо | ETEO-нейминг и тексты-референс |
| D3 | Языки | RU + KK в БД; UI-переключатель — TASK-070 |
| D4 | Конфигуратор | Отдельная страница `/product/{id}` |
| D5 | Тема сайта | Светлая `site.css`; конфигуратор — тёмный `configurator.css` |
| D6 | Slug | ЧПУ через `slug` (категории, серии, кейсы, услуги) |
| D7 | Лиды vs заказы | Разные таблицы: `leads` и `orders` |

---

## Карта зависимостей (кратко)

```
TASK-001..009  БД и seed
       ↓
TASK-010..019  Layout + site.css + site.js
       ↓
TASK-020..029  Контроллеры контента
       ↓
TASK-030..039  Маршрутизация index.php
       ↓
TASK-040..049  Главная
       ↓
TASK-050..059  Каталог + серии
       ↓
TASK-060..069  Кейсы + услуги + FAQ + контакты
       ↓
TASK-070..079  Лиды (API + форма)
       ↓
TASK-080..089  Конфигуратор (доработка)
       ↓
TASK-090..099  Полировка + SEO
```

---

# ФАЗА A — База данных и seed

## TASK-001 — Каталог миграций

- [x] **Зависимости:** нет  
- [ ] **Цель:** подготовить структуру SQL-миграций.

**Файлы:**
- `sql/migrations/001_content_tables.sql` — CREATE новых таблиц
- `sql/migrations/002_alter_products.sql` — ALTER products
- `sql/migrations/003_seed_content.sql` — демо-данные
- `sql/migrate.sh` — скрипт применения миграций по порядку

**Шаги:**
1. Создать папку `sql/migrations/`.
2. Перенести DDL из PLAN §6 в `001_content_tables.sql` (pages, product_series, cases, faq_items, leads, site_settings).
3. В `002_alter_products.sql` добавить `series_id`, `slug` к `products`.
4. `migrate.sh`: выполнять `*.sql` по имени, логировать ошибки.

**Критерии приёмки:**
- [x] `./sql/migrate.sh` отрабатывает без ошибок на чистой БД
- [x] Повторный запуск идемпотентен (IF NOT EXISTS / проверки)

**Проверка:**
```bash
brew services start mysql
mysql -u root -e "SHOW TABLES FROM furniture_platform"
```

**Промпт сессии:**
```text
Реализуй TASK-001: sql/migrations + migrate.sh по docs/IMPLEMENTATION-ROADMAP.md
```

---

## TASK-002 — Таблица `pages`

- [x] **Зависимости:** TASK-001  
- [ ] **Цель:** таблица статического контента.

**Файлы:** `sql/migrations/001_content_tables.sql` (блок `pages`)

**Шаги:**
1. CREATE `pages` (поля из PLAN §6).
2. Индексы: `slug` UNIQUE, `type`, `active`.

**Критерии приёмки:**
- [x] `DESCRIBE pages` — все поля на месте

---

## TASK-003 — Таблица `product_series`

- [x] **Зависимости:** TASK-001  

**Файлы:** `001_content_tables.sql`

**Шаги:**
1. CREATE `product_series`.
2. Подготовить место в seed для 3 серий.

**Критерии приёмки:**
- [x] Таблица создана, FK из products возможен

---

## TASK-004 — Таблица `cases`

- [x] **Зависимости:** TASK-001  

**Файлы:** `001_content_tables.sql`

**Критерии приёмки:**
- [x] `cases.slug` UNIQUE, `active` по умолчанию 1

---

## TASK-005 — Таблица `faq_items`

- [x] **Зависимости:** TASK-001  

**Файлы:** `001_content_tables.sql`

**Критерии приёмки:**
- [x] `sort_order` для сортировки на главной

---

## TASK-006 — Таблица `leads`

- [x] **Зависимости:** TASK-001  

**Файлы:** `001_content_tables.sql`

**Шаги:**
1. CREATE с `source` ENUM и `status` ENUM.

**Критерии приёмки:**
- [x] Можно INSERT тестовой заявки вручную

---

## TASK-007 — Таблица `site_settings`

- [x] **Зависимости:** TASK-001  

**Файлы:** `001_content_tables.sql`

**Шаги:**
1. key-value: phone, email, address, telegram, hero_title, hero_subtitle.

**Критерии приёмки:**
- [x] Минимум 6 ключей в seed

---

## TASK-008 — ALTER `products`

- [x] **Зависимости:** TASK-003  

**Файлы:** `sql/migrations/002_alter_products.sql`

**Шаги:**
1. `series_id` NULL + FK.
2. `slug` VARCHAR(220) NULL → UNIQUE после заполнения seed.

**Критерии приёмки:**
- [x] ETEO-D1 и ETEO-M3 имеют slug и series_id

---

## TASK-009 — Seed контента

- [x] **Зависимости:** TASK-002..008  

**Файлы:** `sql/migrations/003_seed_content.sql`

**Шаги:**
1. **product_series:** `eteo-one`, `eteo-flow`, `eteo-pulse` (name_ru/kk, description).
2. **products:** привязать id=1,2 к сериям; slug `eteo-d1`, `eteo-m3`.
3. **pages:** 4× competence, 4× advantage, 4× service (concept, ergonomics, design, visualization).
4. **faq_items:** 12 вопросов (тексты по референсу eteo.ru, перефразировать).
5. **cases:** 6–8 записей (rosseti, tupolev, wylsacom, agroeko, roscosmos, crtr).
6. **site_settings:** phone, email, address, hero_*.
7. **categories:** убедиться что slug совпадают с маршрутами (`dispetcherskaya` и т.д.).

**Критерии приёмки:**
- [x] `SELECT COUNT(*) FROM faq_items` → 12
- [x] `SELECT COUNT(*) FROM cases WHERE active=1` ≥ 6
- [x] `SELECT * FROM site_settings WHERE setting_key='phone'`

**Проверка:**
```bash
mysql -u root furniture_platform < sql/migrations/003_seed_content.sql
```

**Промпт сессии:**
```text
Реализуй TASK-009: полный seed в 003_seed_content.sql
```

---

# ФАЗА B — Layout и дизайн-система сайта

## TASK-010 — CSS-переменные и базовый `site.css`

- [ ] **Зависимости:** нет (параллельно с A)  

**Файлы:** `public/css/site.css`

**Шаги:**
1. Токены: цвета (#F4F7F9 bg, #5584E8 accent), типографика, радиусы, тени.
2. Reset, `box-sizing`, body, `.container` (max-width 1200px).
3. Утилиты: `.btn`, `.btn--primary`, `.btn--outline`, `.section`, `.grid-2/3/4`.
4. **Не трогать** `configurator.css`.

**Критерии приёмки:**
- [ ] Подключение `site.css` на тестовой HTML-странице даёт светлый фон и синюю кнопку

---

## TASK-011 — Типографика и шрифты

- [ ] **Зависимости:** TASK-010  

**Файлы:** `public/css/site.css`, `views/layouts/main.php`

**Шаги:**
1. Подключить Inter или Manrope (Google Fonts или локально в `public/fonts/`).
2. Стили h1–h4, `.lead`, `.text-muted`.

**Критерии приёмки:**
- [ ] Заголовки и body читаемы, контраст достаточный

---

## TASK-012 — Layout `main.php`

- [ ] **Зависимости:** TASK-010  

**Файлы:** `views/layouts/main.php`

**Шаги:**
1. Параметры: `$pageTitle`, `$pageDescription`, `$bodyClass`, `$extraCss`, `$extraJs`.
2. Структура: `<!DOCTYPE>`, head (meta, site.css), body, слот `<?= $content ?>`, site.js.
3. Хелпер `$e()` для escape — вынести в `views/helpers.php` или включить в layout.

**Критерии приёмки:**
- [ ] Тестовая view рендерится через layout без ошибок PHP

---

## TASK-013 — Partial `header.php`

- [ ] **Зависимости:** TASK-012  

**Файлы:** `views/partials/header.php`, `public/css/site.css` (header/nav)

**Шаги:**
1. Логотип «eteo» → `/`.
2. Burger-кнопка (mobile).
3. Навигация (пока статическая, данные из массива):
   - Диспетчерская мебель (подменю: серии, кресла)
   - Офисная мебель
   - Кейсы → `/cases`
   - Контакты → `/contacts`
4. Телефон и email из `$settings` (позже из БД).

**Критерии приёмки:**
- [ ] Header sticky/fixed, виден на всех страницах с layout

---

## TASK-014 — Partial `footer.php`

- [ ] **Зависимости:** TASK-012  

**Файлы:** `views/partials/footer.php`

**Шаги:**
1. 3–4 колонки ссылок (продукция, услуги, информация).
2. Контакты: phone, email, address.
3. Ссылка `/privacy`.
4. Копирайт.

**Критерии приёмки:**
- [ ] Все ссылки ведут на существующие или заглушечные маршруты

---

## TASK-015 — `site.js`: burger-меню

- [ ] **Зависимости:** TASK-013  

**Файлы:** `public/js/site.js`

**Шаги:**
1. Toggle класса `.nav--open` по клику на burger.
2. Закрытие по Escape и клику вне меню.
3. `aria-expanded` на кнопке.

**Критерии приёмки:**
- [ ] На ширине <768px меню открывается/закрывается

---

## TASK-016 — `site.js`: плавный скролл к якорям

- [ ] **Зависимости:** TASK-015  

**Файлы:** `public/js/site.js`

**Шаги:**
1. Обработчик `a[href^="#"]` для `#lead-form`, `#products`, `#faq`.

**Критерии приёмки:**
- [ ] Клик «Запросить КП» скроллит к форме

---

## TASK-017 — `site.js`: FAQ-аккордеон

- [ ] **Зависимости:** TASK-015  

**Файлы:** `public/js/site.js`, `public/css/site.css` (`.faq-item`)

**Шаги:**
1. Разметка: `.faq-item` > `button.faq-question` + `.faq-answer`.
2. Toggle `.faq-item--open`, один открытый или несколько — **решение:** несколько (как eteo).
3. ARIA: `aria-expanded`, `aria-controls`.

**Критерии приёмки:**
- [ ] Клик раскрывает/скрывает ответ

---

## TASK-018 — Partial `lead-form.php` (разметка)

- [ ] **Зависимости:** TASK-012, TASK-010  

**Файлы:** `views/partials/lead-form.php`

**Шаги:**
1. Поля: email, name, phone, organization, comment.
2. `id="lead-form"`, hidden `source`, placeholder для CSRF.
3. Кнопка «Отправить», ссылка на политику.
4. Блок `.form-message` для success/error (пока пустой).

**Критерии приёмки:**
- [ ] Форма рендерится, поля доступны с клавиатуры

---

## TASK-019 — Интеграция layout в существующие страницы

- [ ] **Зависимости:** TASK-012..014  

**Файлы:** `views/errors/404.php`, `views/errors/500.php`, `views/cart.php`

**Шаги:**
1. Перевести на `layouts/main.php` (ob_start / include pattern).
2. Пример паттерна в комментарии в `views/layouts/main.php`.

**Критерии приёмки:**
- [ ] `/несуществующий` — 404 с header/footer

**Промпт сессии:**
```text
Реализуй TASK-010..019: site.css, layout, header, footer, site.js
```

---

# ФАЗА C — Контроллеры и модели доступа к данным

## TASK-020 — `SiteSettingsRepository` (хелпер)

- [ ] **Зависимости:** TASK-007, TASK-009  

**Файлы:** `controllers/SiteSettings.php` или `includes/SiteSettings.php`

**Шаги:**
1. Метод `getAll(string $locale): array` — key → value.
2. Метод `get(string $key, string $locale, string $default = ''): string`.
3. Кеш в static-переменной на запрос.

**Критерии приёмки:**
- [ ] `get('phone', 'ru')` возвращает номер из seed

---

## TASK-021 — `PageController`

- [ ] **Зависимости:** TASK-002, TASK-009  

**Файлы:** `controllers/PageController.php`

**Шаги:**
1. `getBySlug(string $slug, string $locale): ?array`
2. `getByType(string $type, string $locale): array` — для competence, advantage, service.
3. Только `active = 1`.

**Критерии приёмки:**
- [ ] `getByType('competence', 'ru')` → 4 записи

---

## TASK-022 — `CaseController`

- [ ] **Зависимости:** TASK-004, TASK-009  

**Файлы:** `controllers/CaseController.php`

**Шаги:**
1. `getList(string $locale, int $limit = 0): array`
2. `getBySlug(string $slug, string $locale): ?array`

**Критерии приёмки:**
- [ ] `getList('ru', 3)` — 3 кейса для главной

---

## TASK-023 — `FaqController`

- [ ] **Зависимости:** TASK-005, TASK-009  

**Файлы:** `controllers/FaqController.php`

**Шаги:**
1. `getAll(string $locale): array` ORDER BY sort_order.

**Критерии приёмки:**
- [ ] 12 элементов в порядке sort_order

---

## TASK-024 — `SeriesController`

- [ ] **Зависимости:** TASK-003, TASK-008  

**Файлы:** `controllers/SeriesController.php`

**Шаги:**
1. `getAll(string $locale): array`
2. `getBySlug(string $slug, string $locale): ?array`
3. `getProducts(int $seriesId, string $locale): array` — делегировать ProductController или SQL join.

**Критерии приёмки:**
- [ ] `getBySlug('eteo-one', 'ru')` + список товаров серии

---

## TASK-025 — Расширить `ProductController`: slug и категории

- [ ] **Зависимости:** TASK-008  

**Файлы:** `controllers/ProductController.php`

**Шаги:**
1. `getCategoryBySlug(string $slug, string $locale): ?array`
2. `getProductsByCategorySlug(string $slug, string $locale): array`
3. Сохранить обратную совместимость `getProductsByCategory(int $id)`.

**Критерии приёмки:**
- [ ] `/catalog/dispetcherskaya` данные доступны из контроллера

---

## TASK-026 — `CategoryController` (опционально)

- [ ] **Зависимости:** TASK-025  

**Файлы:** `controllers/CategoryController.php`

**Шаги:**
1. `getAll(string $locale): array` — для хаба `/catalog`.

**Критерии приёмки:**
- [ ] 3 категории с name и slug

---

## TASK-027 — View-helper `render()`

- [ ] **Зависимости:** TASK-012  

**Файлы:** `includes/view.php` или начало `index.php`

**Шаги:**
1. Функция `render(string $view, array $data = [], array $layout = []): void`.
2. Извлекает `$data` в переменные, буферизует view, подключает layout.
3. Глобальные `$settings`, `$locale` в каждом render.

**Критерии приёмки:**
- [ ] `render('home', ['title' => '...'])` без дублирования boilerplate

**Промпт сессии:**
```text
Реализуй TASK-020..027: контроллеры Page, Case, Faq, Series + render helper
```

---

# ФАЗА D — Маршрутизация

## TASK-030 — Рефакторинг парсера URL в `index.php`

- [ ] **Зависимости:** TASK-027  

**Файлы:** `index.php`

**Шаги:**
1. Вынести маршруты в массив/матчер или функцию `dispatch($method, $uri)`.
2. Поддержать сегменты: `$section`, `$param`, `$subParam` (как сейчас).

**Критерии приёмки:**
- [ ] Все старые маршруты работают (регрессия)

---

## TASK-031 — Маршрут `GET /catalog`

- [ ] **Зависимости:** TASK-026, TASK-030  

**Файлы:** `index.php`, `views/catalog/index.php`

**Шаги:**
1. CategoryController → все категории.
2. View: карточки категорий с ссылкой `/catalog/{slug}`.

**Критерии приёмки:**
- [ ] http://localhost:8080/catalog — 200, 3 категории

---

## TASK-032 — Маршрут `GET /catalog/{slug}`

- [ ] **Зависимости:** TASK-025, TASK-031  

**Файлы:** `index.php`, `views/catalog/category.php` (переименовать/заменить catalog.php)

**Шаги:**
1. Распознавать slug (не только int id). Fallback: если numeric — редирект 301 на slug.
2. Список товаров + фильтр серий (sidebar или tabs).

**Критерии приёмки:**
- [ ] `/catalog/dispetcherskaya` показывает ETEO-D1, ETEO-M3

---

## TASK-033 — Маршрут `GET /series/{slug}`

- [ ] **Зависимости:** TASK-024, TASK-030  

**Файлы:** `index.php`, `views/series/show.php`

**Критерии приёмки:**
- [ ] `/series/eteo-one` — описание серии + товары

---

## TASK-034 — Маршрут `GET /cases` и `GET /cases/{slug}`

- [ ] **Зависимости:** TASK-022, TASK-030  

**Файлы:** `index.php`, `views/cases/index.php`, `views/cases/show.php`

**Критерии приёмки:**
- [ ] Список кейсов; деталь одного кейса; 404 для несуществующего slug

---

## TASK-035 — Маршрут `GET /services/{slug}`

- [ ] **Зависимости:** TASK-021, TASK-030  

**Файлы:** `index.php`, `views/services/show.php`

**Критерии приёмки:**
- [ ] `/services/concept` — страница услуги из `pages`

---

## TASK-036 — Маршруты `GET /faq`, `GET /contacts`, `GET /privacy`

- [ ] **Зависимости:** TASK-021, TASK-023, TASK-020  

**Файлы:** `views/faq.php`, `views/contacts.php`, `views/pages/privacy.php`

**Критерии приёмки:**
- [ ] Три страницы с layout; FAQ использует аккордеон

---

## TASK-037 — Обновить `GET /` для маркетинговой главной

- [ ] **Зависимости:** TASK-040 (контент главной)  

**Файлы:** `index.php`

**Шаги:**
1. Загружать данные для всех секций (settings, pages, categories, cases, faq).
2. Передать в `views/home.php`.

**Критерии приёмки:**
- [ ] `/` не ломается, отдаёт полную главную

---

## TASK-038 — Редиректы со старых URL

- [ ] **Зависимости:** TASK-032  

**Файлы:** `index.php`

**Шаги:**
1. `/catalog/1` → 301 `/catalog/dispetcherskaya` (маппинг id→slug из БД).

**Критерии приёмки:**
- [ ] Старые закладки работают

**Промпт сессии:**
```text
Реализуй TASK-030..038: все GET-маршруты в index.php
```

---

# ФАЗА E — Главная страница (секции)

> Каждая секция — отдельная задача для узких AI-сессий.

## TASK-040 — Секция Hero

- [ ] **Зависимости:** TASK-012, TASK-020, TASK-037  

**Файлы:** `views/home.php`, `views/partials/sections/hero.php`, `public/css/site.css`

**Шаги:**
1. Заголовок и подзаголовок из `site_settings` (hero_title, hero_subtitle).
2. CTA: «Подробнее» → `#products`, «Запросить КП» → `#lead-form`, «Рассчитать в конфигураторе» → `/product/1`.
3. Опционально: фоновое изображение `public/images/hero.webp` (placeholder допустим).

**Критерии приёмки:**
- [ ] Три кнопки кликабельны и ведут куда нужно

---

## TASK-041 — Секция «Компетенции»

- [ ] **Зависимости:** TASK-021, TASK-040  

**Файлы:** `views/partials/sections/competences.php`

**Шаги:**
1. 4 карточки из `pages` type=competence.
2. Ссылка на `/services/{slug}` если есть соответствие.

**Критерии приёмки:**
- [ ] 4 блока в сетке 2×2 (desktop), 1 колонка (mobile)

---

## TASK-042 — Секция «Направления» (3 продукта)

- [ ] **Зависимости:** TASK-026, TASK-040  

**Файлы:** `views/partials/sections/directions.php`

**Шаги:**
1. Карточки: Диспетчерские пульты, Кресла (заглушка/категория), Офисные решения.
2. Ссылки на `/catalog/{slug}`.

**Критерии приёмки:**
- [ ] `id="products"` для якоря «Подробнее»

---

## TASK-043 — Секция «Преимущества»

- [ ] **Зависимости:** TASK-021, TASK-040  

**Файлы:** `views/partials/sections/advantages.php`

**Критерии приёмки:**
- [ ] 4 тезиса с иконками (SVG inline или CSS)

---

## TASK-044 — Секция «Форма КП» на главной

- [ ] **Зависимости:** TASK-018, TASK-070 (или заглушка submit)  

**Файлы:** `views/partials/sections/lead.php`

**Шаги:**
1. Include `lead-form.php` с `source=home`.
2. Заголовок «Рассчитать ваш проект».

**Критерии приёмки:**
- [ ] Форма видна на главной, id="lead-form"

---

## TASK-045 — Секция «Кейсы» (превью)

- [ ] **Зависимости:** TASK-022, TASK-040  

**Файлы:** `views/partials/sections/cases-preview.php`

**Шаги:**
1. 2–3 кейса, ссылка «Все кейсы» → `/cases`.

**Критерии приёмки:**
- [ ] Клик по кейсу → `/cases/{slug}`

---

## TASK-046 — Секция FAQ на главной

- [ ] **Зависимости:** TASK-023, TASK-017, TASK-040  

**Файлы:** `views/partials/sections/faq.php`

**Шаги:**
1. Include partial; `id="faq"`.
2. Ссылка «Все вопросы» → `/faq` (опционально).

**Критерии приёмки:**
- [ ] 12 вопросов, аккордеон работает

---

## TASK-047 — Секция «Контакты» на главной

- [ ] **Зависимости:** TASK-020, TASK-040  

**Файлы:** `views/partials/sections/contacts-strip.php`

**Критерии приёмки:**
- [ ] Телефон, email, адрес из site_settings

---

## TASK-048 — Сборка `home.php` из секций

- [ ] **Зависимости:** TASK-040..047  

**Файлы:** `views/home.php`

**Шаги:**
1. Удалить старую «только сетку товаров» или перенести в `#products` как мини-каталог.
2. Последовательный include всех section-partials.

**Критерии приёмки:**
- [ ] Главная визуально близка к структуре eteo.ru (скриншот-сравнение)

**Промпт сессии:**
```text
Реализуй TASK-040..048: все секции главной
```

---

# ФАЗА F — Каталог и серии

## TASK-050 — View хаба `/catalog`

- [ ] **Зависимости:** TASK-031  

**Файлы:** `views/catalog/index.php`

**Критерии приёмки:**
- [ ] Карточки категорий с изображением-placeholder

---

## TASK-051 — View категории `/catalog/{slug}`

- [ ] **Зависимости:** TASK-032  

**Файлы:** `views/catalog/category.php`

**Шаги:**
1. Breadcrumb: Главная / Категория.
2. Сетка товаров: name, sku, «от {price}», ссылка на product.

**Критерии приёмки:**
- [ ] Цена форматируется с пробелами и ₸

---

## TASK-052 — Фильтр по серии в каталоге

- [ ] **Зависимости:** TASK-051, TASK-024  

**Файлы:** `views/catalog/category.php`, `index.php` (query `?series=eteo-one`)

**Критерии приёмки:**
- [ ] Фильтр сужает список товаров

---

## TASK-053 — View `/series/{slug}`

- [ ] **Зависимости:** TASK-033  

**Файлы:** `views/series/show.php`

**Шаги:**
1. Hero серии, описание, сетка товаров серии.
2. CTA: конфигуратор + запрос КП.

**Критерии приёмки:**
- [ ] ETEO-One page показывает ETEO-D1

---

## TASK-054 — Placeholder-изображения каталога

- [ ] **Зависимости:** TASK-051  

**Файлы:** `public/images/placeholder.webp`, обновить seed `image_preview`

**Критерии приёмки:**
- [ ] Нет битых img на каталоге

**Промпт сессии:**
```text
Реализуй TASK-050..054: каталог и серии
```

---

# ФАЗА G — Кейсы, услуги, статика

## TASK-060 — View списка кейсов

- [ ] **Зависимости:** TASK-034  

**Файлы:** `views/cases/index.php`

**Шаги:**
1. Сетка карточек, внизу lead-form (source=cases).

**Критерии приёмки:**
- [ ] ≥6 кейсов на странице

---

## TASK-061 — View детали кейса

- [ ] **Зависимости:** TASK-034  

**Файлы:** `views/cases/show.php`

**Шаги:**
1. Заголовок, клиент, body, галерея (1 img достаточно для MVP).
2. Блок «Смотрите также» — 3 ссылки на серии.

**Критерии приёмки:**
- [ ] `/cases/rosseti` открывается

---

## TASK-062 — View услуги `/services/{slug}`

- [ ] **Зависимости:** TASK-035  

**Файлы:** `views/services/show.php`

**Критерии приёмки:**
- [ ] 4 услуги открываются без 404

---

## TASK-063 — Страница `/faq`

- [ ] **Зависимости:** TASK-036, TASK-023  

**Файлы:** `views/faq.php`

**Критерии приёмки:**
- [ ] Полный список FAQ с аккордеоном

---

## TASK-064 — Страница `/contacts`

- [ ] **Зависимости:** TASK-036, TASK-018  

**Файлы:** `views/contacts.php`

**Шаги:**
1. Контакты + lead-form (source=contacts).

**Критерии приёмки:**
- [ ] Форма и контакты на одной странице

---

## TASK-065 — Страница `/privacy`

- [ ] **Зависимости:** TASK-036  

**Файлы:** `views/pages/privacy.php`, seed `pages` slug=privacy type=static

**Критерии приёмки:**
- [ ] Текст политики из БД или статический блок

**Промпт сессии:**
```text
Реализуй TASK-060..065: кейсы, услуги, faq, contacts, privacy
```

---

# ФАЗА H — Лиды (форма КП + API)

## TASK-070 — `LeadController` + валидация

- [ ] **Зависимости:** TASK-006  

**Файлы:** `controllers/LeadController.php`

**Шаги:**
1. Метод `createLead(): void` — JSON ответ.
2. Поля: email (filter_var), name (trim, len), phone (regex), organization, comment, source.
3. CSRF — переиспользовать паттерн OrderController.
4. htmlspecialchars перед INSERT.

**Критерии приёмки:**
- [ ] Невалидный email → 422 JSON error
- [ ] Валидный → 201 + `{ "id": N }`

---

## TASK-071 — Маршрут `POST /api/lead/create`

- [ ] **Зависимости:** TASK-070, TASK-030  

**Файлы:** `index.php`

**Критерии приёмки:**
- [ ] curl POST с CSRF создаёт запись в `leads`

---

## TASK-072 — `site.js`: отправка lead-form

- [ ] **Зависимости:** TASK-071, TASK-018  

**Файлы:** `public/js/site.js`

**Шаги:**
1. Fetch CSRF → POST JSON.
2. UI: loading, success, error на `.form-message`.
3. Маска телефона (простая, без внешних lib).

**Критерии приёмки:**
- [ ] Отправка с главной не перезагружает страницу
- [ ] Запись в БД с `source=home`

---

## TASK-073 — Lead-form на `/cases` и `/contacts`

- [ ] **Зависимости:** TASK-072, TASK-060, TASK-064  

**Файлы:** те же views

**Шаги:**
1. Передавать `source` в hidden field.

**Критерии приёмки:**
- [ ] source корректен в БД для каждой страницы

---

## TASK-074 — Email-уведомление (опционально)

- [ ] **Зависимости:** TASK-070  

**Файлы:** `config/config.php` (smtp), `controllers/LeadController.php`

**Шаги:**
1. `mail()` или PHPMailer если есть.
2. Пропускать без ошибки если SMTP не настроен (log only).

**Критерии приёмки:**
- [ ] При `MAIL_ENABLED=false` заказ не падает

**Промпт сессии:**
```text
Реализуй TASK-070..074: LeadController + API + JS форма
```

---

# ФАЗА I — Доработка конфигуратора

## TASK-080 — Подключить layout к product page (частично)

- [ ] **Зависимости:** TASK-012  

**Файлы:** `views/product/card.php`

**Шаги:**
1. **Решение:** header/footer из site layout, body — тёмный configurator.
2. Подключить оба CSS: site.css (header/footer) + configurator.css (контент).

**Критерии приёмки:**
- [ ] Единая навигация на странице товара
- [ ] Конфигуратор визуально не «ломается»

---

## TASK-081 — Галерея thumbnails в `card.php`

- [ ] **Зависимости:** TASK-080  

**Файлы:** `views/product/card.php`, `public/images/` (3 thumb + main)

**Шаги:**
1. Разметка `.gallery__thumbnails` как в `card.html`.
2. 2–3 placeholder изображения.

**Критерии приёмки:**
- [ ] Переключение фото работает (JS уже в configurator.js)

---

## TASK-082 — Блок «Запросить КП» на странице товара

- [ ] **Зависимости:** TASK-018, TASK-072  

**Файлы:** `views/product/card.php`

**Шаги:**
1. Под конфигуратором: «Или оставьте заявку менеджеру» + compact lead-form, source=product.

**Критерии приёмки:**
- [ ] Две воронки: заказ через модал + лид-форма

---

## TASK-083 — Breadcrumb со slug категории

- [ ] **Зависимости:** TASK-025, TASK-080  

**Файлы:** `views/product/card.php`

**Шаги:**
1. Ссылка категории → `/catalog/{category_slug}`.

**Критерии приёмки:**
- [ ] Breadcrumb кликабелен

---

## TASK-084 — SEO meta на странице товара

- [ ] **Зависимости:** TASK-080  

**Файлы:** `views/product/card.php`, layout

**Критерии приёмки:**
- [ ] Уникальный `<title>` и `meta description`

**Промпт сессии:**
```text
Реализуй TASK-080..084: доработка product page
```

---

# ФАЗА J — Полировка

## TASK-090 — Переключатель RU/KK в header

- [ ] **Зависимости:** TASK-013  

**Файлы:** `views/partials/header.php`, `index.php` (уже есть locale logic)

**Критерии приёмки:**
- [ ] `?lang=kk` меняет тексты интерфейса и контента из БД

---

## TASK-091 — 404/500 в layout

- [ ] **Зависимости:** TASK-019  

**Файлы:** `views/errors/404.php`, `500.php`

**Критерии приёмки:**
- [ ] Единый вид ошибок

---

## TASK-092 — `sitemap.xml` (генератор)

- [ ] **Зависимости:** TASK-038  

**Файлы:** `index.php` (маршрут GET /sitemap.xml) или `public/sitemap.xml.php`

**Шаги:**
1. URL: главная, каталог, категории, товары, кейсы, услуги.

**Критерии приёмки:**
- [ ] /sitemap.xml отдаёт valid XML

---

## TASK-093 — Open Graph теги

- [ ] **Зависимости:** TASK-012  

**Файлы:** `views/layouts/main.php`

**Критерии приёмки:**
- [ ] og:title, og:description на главной и product

---

## TASK-094 — Lazy-load изображений

- [ ] **Зависимости:** TASK-054  

**Файлы:** views с `<img loading="lazy">` где не LCP

**Критерии приёмки:**
- [ ] Hero — eager, остальные lazy

---

## TASK-095 — Регрессионный чеклист

- [ ] **Зависимости:** все MVP задачи  

**Файлы:** `docs/QA-CHECKLIST.md`

**Шаги:**
1. Список URL и ожидаемый статус.
2. curl-команды для API order + lead.

**Критерии приёмки:**
- [ ] Все пункты чеклиста проходят

**Промпт сессии:**
```text
Реализуй TASK-090..095: i18n, SEO, QA checklist
```

---

# Пакеты задач (для широких сессий)

Если нужно выполнить больше за одну сессию:

| Пакет | Задачи | Оценка |
|-------|--------|--------|
| **P0-DB** | TASK-001..009 | 1 сессия |
| **P1-Shell** | TASK-010..019 | 1–2 сессии |
| **P2-Backend** | TASK-020..027 | 1 сессия |
| **P3-Routes** | TASK-030..038 | 1 сессия |
| **P4-Home** | TASK-040..048 | 2 сессии |
| **P5-Catalog** | TASK-050..054 | 1 сессия |
| **P6-Content** | TASK-060..065 | 1 сессия |
| **P7-Leads** | TASK-070..074 | 1 сессия |
| **P8-Product** | TASK-080..084 | 1 сессия |
| **P9-Polish** | TASK-090..095 | 1 сессия |

---

# Чеклист MVP (финальный)

Отметь когда все задачи пакетов P0–P8 выполнены:

- [ ] **P0-DB** — миграции и seed
- [ ] **P1-Shell** — layout, site.css, site.js
- [ ] **P2-Backend** — контроллеры
- [ ] **P3-Routes** — все GET-маршруты
- [ ] **P4-Home** — маркетинговая главная
- [ ] **P5-Catalog** — каталог + серии
- [ ] **P6-Content** — кейсы, услуги, faq, contacts
- [ ] **P7-Leads** — API и формы КП
- [ ] **P8-Product** — доработка конфигуратора
- [ ] **P9-Polish** — i18n, SEO, QA

---

# Быстрые команды

```bash
# Запуск
./scripts/dev.sh

# Миграции (после TASK-001)
./sql/migrate.sh

# Проверка API заказа (существует)
curl -s http://localhost:8080/api/csrf-token

# Проверка API лида (после TASK-071)
curl -s -X POST http://localhost:8080/api/lead/create \
  -H "Content-Type: application/json" \
  -d '{"email":"test@test.com","name":"Test","phone":"+79990001122"}'
```

---

# История изменений roadmap

| Дата | Изменение |
|------|-----------|
| 2026-05-22 | Первая версия: 95 атомарных задач, 10 фаз |
