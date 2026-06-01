# План: корпоративный сайт по референсу eteo.ru

> Референс: [eteo.ru](https://eteo.ru/)  
> Локальный проект: `furniture_platform`  
> Дата: май 2026

**Пошаговая реализация (для AI-сессий):**  
- **Гайд для студента (защита):** [GUIDE-MENYON.md](./GUIDE-MENYON.md)  
- **Диплом / оптимум (~28 задач):** [IMPLEMENTATION-OPTIMUM.md](./IMPLEMENTATION-OPTIMUM.md)  
- **Полная версия (~95 задач):** [IMPLEMENTATION-ROADMAP.md](./IMPLEMENTATION-ROADMAP.md)

---

## Содержание

1. [Как устроен eteo.ru](#1-как-устроен-eteoru)
2. [Сравнение с локальным проектом](#2-сравнение-с-локальным-проектом)
3. [Целевая архитектура](#3-целевая-архитектура)
4. [План по фазам](#4-план-по-фазам)
5. [Маршруты и API](#5-маршруты-и-api)
6. [Схема базы данных](#6-схема-базы-данных)
7. [MVP и приоритеты](#7-mvp-и-приоритеты)
8. [Ключевое отличие продукта](#8-ключевое-отличие-продукта)

---

## 1. Как устроен eteo.ru

### 1.1 Технология

Сайт собран на **Tilda CMS** (не кастомный backend):

| Компонент | Реализация |
|-----------|------------|
| Страницы | Статические блоки `tilda-blocks-page*.css/js` |
| Формы | `tilda-forms` + маска телефона |
| Меню | Burger + выпадающие подменю (`tilda-menusub`) |
| Анимации | Scroll-анимации (`tilda-animation`) |
| Аналитика | Яндекс.Метрика, Google Analytics |
| Медиа | CDN `tildacdn.com`, WebP-изображения |

**Вывод:** это маркетинговый лендинг + набор посадочных страниц, без собственной БД и API заказов.

### 1.2 Информационная архитектура

```
Навигация (header)
├── Диспетчерская мебель
│   ├── Обзор серий
│   ├── ETEO Ван / One
│   ├── ETEO Флоу / Flow
│   ├── ETEO Пульс / Pulse
│   └── Диспетчерские кресла
├── Офисная мебель
│   ├── Конференц-столы
│   └── Мебель для офиса
├── Смарт ИТ
├── Дизайн
└── Кейсы

Главная (одна длинная страница)
├── Hero + CTA
├── Компетенции (4 блока)
├── 3 направления: пульты / кресла / офис
├── Преимущества (4 тезиса)
├── Форма «Рассчитать проект» / КП
├── Кейсы (превью)
├── FAQ (аккордеон)
└── Контакты + футер

Отдельные страницы
├── /cases - портфолио
├── Серии продуктов (Van, Flow, Pulse)
├── Услуги дизайна
└── Статика: политика, контакты, новости
```

### 1.3 Поведение и UX

| Элемент | Как работает |
|---------|--------------|
| **Hero** | Заголовок, описание, 2 CTA: «Подробнее» и «Запросить КП» |
| **Компетенции** | 4 карточки услуг (концепция, эргономика, дизайн, визуализация) |
| **Продуктовые блоки** | 3 крупные карточки-направления со ссылками «Подробнее» |
| **Преимущества** | 4 тезиса (производство, материалы, кастом, ГОСТ) |
| **Лид-форма** | Повторяется на главной и в `/cases`: email, имя, телефон, организация, комментарий |
| **Кейсы** | Сетка проектов → детальные страницы |
| **FAQ** | Аккордеон из 12 вопросов на главной |
| **Контакты** | Телефон, email, адрес, Telegram в футере |
| **Конфигуратор** | **Отсутствует** - цена и заказ только через менеджера / КП |

### 1.4 Визуальный стиль

- Светлая тема (`#F4F7F9`), белый header
- Шрифт Tilda Sans, много «воздуха»
- Синий акцент на кнопках (`#5584E8`)
- Фото продукции и проектов в WebP

> **Примечание:** в локальном проекте сейчас тёмная индустриальная тема (`public/css/configurator.css`). Для корпоративных страниц нужна отдельная светлая дизайн-система.

---

## 2. Сравнение с локальным проектом

| Функция eteo.ru | Локально | Готовность |
|-----------------|----------|------------|
| Главная-лендинг | `views/home.php` - только сетка товаров | ~15% |
| Каталог по категориям | `/catalog/{id}` | ~40% |
| Карточка серии/товара | `/product/{id}` + конфигуратор | **сильнее eteo** |
| Онлайн-заказ с опциями | `configurator.js` + `POST /api/order/create` | **уникально** |
| Форма КП | Нет | 0% |
| Кейсы | Нет | 0% |
| FAQ | Нет | 0% |
| Общий layout (header/footer) | Нет | 0% |
| Slug-URL (`/catalog/dispetcherskaya`) | Только numeric ID | 0% |
| RU / KK | В БД есть, в UI нет переключателя | ~30% |

### Что уже реализовано хорошо

- Front controller (`index.php`) + маршрутизация
- `ProductController` - товары, опции, JSON API
- `OrderController` - CSRF, валидация, транзакции, расчёт цены
- Конфигуратор: live price, модальное оформление заказа
- Безопасность: prepared statements, XSS-экранирование, HTTP-заголовки

### Чего не хватает

- Маркетинговая главная (hero, компетенции, преимущества)
- Портфолио (кейсы)
- FAQ
- Форма заявки на КП (отдельно от заказа)
- Общий layout, навигация, футер
- Slug-маршруты, страницы услуг, контакты

**Итог:** локальный проект - **B2B-витрина с конфигуратором**; eteo.ru - **корпоративный маркетинг + лиды**. Нужно объединить оба подхода.

---

## 3. Целевая архитектура

```
┌─────────────────────────────────────────────────────────┐
│  Presentation (views + public/css|js)                   │
│  home | catalog | series | product+configurator         │
│  cases | services | faq | contacts                      │
└────────────────────┬────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────┐
│  Controllers                                            │
│  Product | Order | Lead | Page | Case | Faq             │
└────────────────────┬────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────┐
│  MySQL                                                  │
│  products, options, orders, leads, cases, pages         │
└─────────────────────────────────────────────────────────┘
```

### Структура файлов (целевая)

```
furniture_platform/
├── index.php
├── router.php
├── config/
├── controllers/
│   ├── ProductController.php   # есть
│   ├── OrderController.php     # есть
│   ├── LeadController.php      # новый
│   ├── PageController.php      # новый
│   ├── CaseController.php      # новый
│   └── FaqController.php       # новый
├── views/
│   ├── layouts/
│   │   └── main.php
│   ├── partials/
│   │   ├── header.php
│   │   ├── footer.php
│   │   └── lead-form.php
│   ├── home.php                # переработать
│   ├── catalog/
│   ├── product/
│   ├── cases/
│   ├── services/
│   ├── faq.php
│   └── contacts.php
├── public/
│   ├── css/
│   │   ├── site.css            # светлая корпоративная тема
│   │   └── configurator.css    # тёмная тема конфигуратора
│   ├── js/
│   │   ├── site.js
│   │   └── configurator.js
│   └── images/
└── sql/
    ├── schema.sql
    └── migrations/
```

---

## 4. План по фазам

### Фаза 0 - Цели и границы (1-2 дня)

**Решить до старта:**

1. Копируем структуру и контент eteo.ru или только UX-паттерны?
2. Бренд: ETEO-нейминг в демо или свой?
3. Языки: RU + KK с первого дня?
4. Конфигуратор: отдельная страница товара (как сейчас) или встроен в серии?

**Рекомендация:** структура eteo.ru + конфигуратор как ключевое отличие («Рассчитать онлайн» рядом с «Запросить КП»).

---

### Фаза 1 - Фундамент (3-5 дней)

#### 1.1 Общий layout

| Файл | Назначение |
|------|------------|
| `views/layouts/main.php` | Обёртка: header, footer, meta, скрипты |
| `views/partials/header.php` | Логотип, меню, burger, переключатель языка |
| `views/partials/footer.php` | Навигация, контакты, политика |
| `views/partials/lead-form.php` | Форма заявки на КП |
| `public/css/site.css` | Светлая корпоративная тема |
| `public/js/site.js` | Меню, FAQ-аккордеон, якоря, маска телефона |

#### 1.2 Задачи

- [ ] Вынести повторяющуюся разметку в layout
- [ ] Подключить `site.css` на всех маркетинговых страницах
- [ ] Оставить `configurator.css` только на `/product/{id}`
- [ ] Исправить автозагрузку и `getenv()` (уже сделано частично)

---

### Фаза 2 - Главная как eteo.ru (5-7 дней)

Секции `views/home.php`:

| # | Секция | Источник данных |
|---|--------|-----------------|
| 1 | Hero + 2 CTA | `site_settings` / статика |
| 2 | Компетенции (4 карточки) | `pages` type=competence |
| 3 | Направления (пульты / кресла / офис) | `categories` |
| 4 | Преимущества (4 тезиса) | `pages` type=advantage |
| 5 | Форма КП | `partials/lead-form.php` |
| 6 | Кейсы (2-3 превью) | `cases` LIMIT 3 |
| 7 | FAQ (аккордеон) | `faq_items` |
| 8 | Контакты | `site_settings` |

**CTA на главной:**

- «Подробнее» → `#products` или `/catalog`
- «Запросить КП» → форма (якорь `#lead-form`)
- «Рассчитать в конфигураторе» → `/product/1` *(уникально для нашего проекта)*

---

### Фаза 3 - Каталог и серии (4-6 дней)

#### 3.1 Модель «серий» (Van / Flow / Pulse)

- Таблица `product_series`
- Поле `series_id` у `products`
- Страница `/series/{slug}`: hero, описание, список моделей

#### 3.2 Каталог

| URL | Содержимое |
|-----|------------|
| `/catalog` | Хаб: карточки всех категорий |
| `/catalog/{slug}` | Товары категории + фильтр по серии |
| `/product/{id}` | Конфигуратор (уже есть) |

#### 3.3 Доработка конфигуратора

- [ ] Галерея с thumbnails (JS уже есть, разметки нет)
- [ ] Блок «или оставьте заявку менеджеру»
- [ ] Хлебные крошки через slug категории

---

### Фаза 4 - Кейсы и услуги (3-4 дня)

#### Кейсы (`/cases`)

По образцу [eteo.ru/cases](https://eteo.ru/cases):

- Сетка проектов: фото, клиент, краткое описание
- Детальная страница `/cases/{slug}`
- Внизу: форма КП + блок «Смотрите также» (ссылки на серии)

#### Услуги

| URL | Тип |
|-----|-----|
| `/services/concept` | Разработка концепции |
| `/services/ergonomics` | Эргономика |
| `/services/design` | Дизайн-проект |
| `/services/visualization` | Визуализация |

Данные из таблицы `pages` с `type = 'service'`.

---

### Фаза 5 - Формы и backend (3-4 дня)

| Форма | Endpoint | Таблица |
|-------|----------|---------|
| Заявка на КП | `POST /api/lead/create` | `leads` |
| Заказ из конфигуратора | `POST /api/order/create` | `orders` *(есть)* |
| Обратная связь | `POST /api/lead/create` | `leads` (`source=contact`) |

**LeadController:**

- Валидация полей (email, телефон, имя)
- CSRF (как в `OrderController`)
- XSS-экранирование
- Запись в `leads`
- Email-уведомление менеджеру (SMTP / `mail()`)

**Админка (опционально, фаза 2):**

- Просмотр `leads` и `orders`
- CRUD для `cases`, `faq_items`, `pages`

---

### Фаза 6 - Дизайн и медиа (4-5 дней, параллельно)

| Задача | Детали |
|--------|--------|
| Светлая тема | `site.css` - отдельно от `configurator.css` |
| Шрифты | Inter / Manrope (аналог Tilda Sans) |
| Изображения | `public/images/` - hero, категории, кейсы, webp |
| Адаптив | Mobile-first, burger-меню |
| SEO | `title` / `description`, Open Graph, `sitemap.xml` |
| Доступность | focus states, `aria` для FAQ и меню |

---

### Фаза 7 - Полировка (2-3 дня)

- [ ] Переключатель RU/KK в header (`?lang=ru|kk`)
- [ ] `/privacy` - политика конфиденциальности
- [ ] 404/500 в общем layout
- [ ] Lazy-load изображений
- [ ] Базовые тесты API (lead, order)

---

## 5. Маршруты и API

### Страницы (GET)

| Метод | Маршрут | View / Controller |
|-------|---------|-------------------|
| GET | `/` | `home.php` - маркетинговая главная |
| GET | `/catalog` | `catalog/index.php` - хаб категорий |
| GET | `/catalog/{slug}` | `catalog/category.php` |
| GET | `/series/{slug}` | `series/show.php` |
| GET | `/product/{id}` | `product/card.php` + конфигуратор |
| GET | `/cases` | `cases/index.php` |
| GET | `/cases/{slug}` | `cases/show.php` |
| GET | `/services/{slug}` | `services/show.php` |
| GET | `/faq` | `faq.php` |
| GET | `/contacts` | `contacts.php` |
| GET | `/privacy` | `pages/privacy.php` |
| GET | `/cart` | `cart.php` (stub или полноценная корзина) |
| GET | `/order/success/{id}` | `order/success.php` *(есть)* |

### API

| Метод | Маршрут | Назначение |
|-------|---------|------------|
| GET | `/api/product/{id}` | JSON товара *(есть)* |
| GET | `/api/csrf-token` | CSRF-токен *(есть)* |
| POST | `/api/order/create` | Создание заказа *(есть)* |
| POST | `/api/lead/create` | Заявка на КП *(новый)* |

---

## 6. Схема базы данных

### Новые таблицы

```sql
-- Статические страницы и блоки контента
CREATE TABLE pages (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug        VARCHAR(220) NOT NULL UNIQUE,
    type        ENUM('service','competence','advantage','static') NOT NULL,
    title_ru    VARCHAR(300) NOT NULL,
    title_kk    VARCHAR(300) NOT NULL,
    excerpt_ru  TEXT,
    excerpt_kk  TEXT,
    body_ru     MEDIUMTEXT,
    body_kk     MEDIUMTEXT,
    image       VARCHAR(500),
    sort_order  SMALLINT UNSIGNED DEFAULT 0,
    active      TINYINT(1) DEFAULT 1
);

-- Серии продуктов (Van, Flow, Pulse)
CREATE TABLE product_series (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug        VARCHAR(220) NOT NULL UNIQUE,
    name_ru     VARCHAR(200) NOT NULL,
    name_kk     VARCHAR(200) NOT NULL,
    description_ru TEXT,
    description_kk TEXT,
    image       VARCHAR(500),
    sort_order  SMALLINT UNSIGNED DEFAULT 0
);

-- Портфолио / кейсы
CREATE TABLE cases (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug        VARCHAR(220) NOT NULL UNIQUE,
    client      VARCHAR(200) NOT NULL,
    title_ru    VARCHAR(300) NOT NULL,
    title_kk    VARCHAR(300) NOT NULL,
    excerpt_ru  TEXT,
    excerpt_kk  TEXT,
    body_ru     MEDIUMTEXT,
    body_kk     MEDIUMTEXT,
    image       VARCHAR(500),
    sort_order  SMALLINT UNSIGNED DEFAULT 0,
    active      TINYINT(1) DEFAULT 1,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- FAQ
CREATE TABLE faq_items (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question_ru VARCHAR(500) NOT NULL,
    question_kk VARCHAR(500) NOT NULL,
    answer_ru   TEXT NOT NULL,
    answer_kk   TEXT NOT NULL,
    sort_order  SMALLINT UNSIGNED DEFAULT 0,
    active      TINYINT(1) DEFAULT 1
);

-- Заявки на КП (лиды)
CREATE TABLE leads (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email       VARCHAR(255) NOT NULL,
    name        VARCHAR(150) NOT NULL,
    phone       VARCHAR(50) NOT NULL,
    organization VARCHAR(200),
    comment     TEXT,
    source      ENUM('home','cases','contacts','product') DEFAULT 'home',
    status      ENUM('new','contacted','done') DEFAULT 'new',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Настройки сайта (контакты, соцсети)
CREATE TABLE site_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    value_ru    TEXT,
    value_kk    TEXT
);
```

### Изменения существующих таблиц

```sql
ALTER TABLE products
    ADD COLUMN series_id INT UNSIGNED NULL,
    ADD COLUMN slug VARCHAR(220) NULL,
    ADD CONSTRAINT fk_products_series
        FOREIGN KEY (series_id) REFERENCES product_series(id)
        ON DELETE SET NULL;
```

### Seed-данные (минимум)

- 3 серии: `eteo-one`, `eteo-flow`, `eteo-pulse`
- 12 FAQ-пунктов (по референсу eteo.ru)
- 6-8 кейсов (Россети, Туполев, Wylsacom и др.)
- `site_settings`: телефон, email, адрес
- 4 страницы услуг

---

## 7. MVP и приоритеты

### Срок: ~2 недели

| Неделя | Задачи |
|--------|--------|
| **1** | Layout + `site.css` + миграции БД + seed |
| **1-2** | Главная (все секции) + форма КП + `LeadController` |
| **2** | Каталог по slug + серии |
| **2** | Кейсы + FAQ + контакты |
| **2** | Доработка конфигуратора (галерея, CTA) |

### Must have (MVP)

- [x] ~~Конфигуратор и API заказов~~ (уже есть)
- [ ] Общий layout (header/footer)
- [ ] Маркетинговая главная
- [ ] Каталог по slug
- [ ] Кейсы (список + деталь)
- [ ] FAQ
- [ ] Форма КП + API лидов
- [ ] Контакты

### Nice to have (после MVP)

- Админ-панель
- Раздел «Новости»
- Smart IT
- Яндекс.Метрика / GA
- Email-рассылка при новом лиде

### Не копировать из eteo.ru

- Платформу Tilda
- Отсутствие онлайн-расчёта цены
- Полностью статичный контент без БД

---

## 8. Ключевое отличие продукта

| | eteo.ru | Наш локальный проект |
|---|---------|----------------------|
| **Путь клиента** | Увидел → заявка → менеджер считает | Увидел → конфигуратор → цена → заказ |
| **Цена** | Только после КП | Онлайн, с учётом опций |
| **Заказ** | Через менеджера | `POST /api/order/create` |
| **Конфигуратор** | Нет | Есть (`configurator.js`) |

### Рекомендуемые CTA на главной

```
[ Подробнее ]  [ Запросить КП ]  [ Рассчитать в конфигураторе → ]
```

Третья кнопка - **конкурентное преимущество**, которого нет на eteo.ru.

---

## Запуск локально (напоминание)

```bash
# MySQL
brew services start mysql

# Сервер разработки
./scripts/dev.sh
# → http://localhost:8080
```

Docker (когда Docker Desktop запущен):

```bash
docker compose up -d
# → http://localhost:8080
```

---

## Следующий шаг

Рекомендуемый старт: **Фаза 1** (layout + `site.css` + миграции + seed) → **Фаза 2** (главная с hero, FAQ, формой КП).
