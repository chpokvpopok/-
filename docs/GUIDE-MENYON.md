# Гайд для Менёна: как доделать диплом и защитить проект

Привет. Этот файл — твоя **главная инструкция**. Тут нет «магии»: по шагам, какие документы открывать, как работать с Cursor (вайбкодинг), что уже сделано за тебя и что **нужно сделать тебе**, чтобы на защите всё работало.

Наставник помог с архитектурой и базой. **Защищаешь ты** — значит, ты должен уметь **запустить проект**, **пройти демо** и **объяснить**, что где лежит.

### Карта на одной строке

```
ЧАТ0 проверка → ЧАТ1 O2 layout → ЧАТ2 O3 главная → ЧАТ3 O4 каталог → ЧАТ4 O5 форма → ЧАТ5 O6 финал
```

**Все промпты для копипаста — раздел 7.** Не придумывай сам, копируй оттуда.

---

## 1. О чём вообще проект (одним абзацем)

**Веб-платформа мебельного производства** (референс по виду — [eteo.ru](https://eteo.ru/), но мы не копируем сайт один в один).

**Фишка диплома:** не просто витрина, а **конфигуратор** — пользователь выбирает опции (материал, LED и т.д.), видит **цену в реальном времени** и может **оформить заказ** в MySQL. Плюс простая **форма «Запросить КП»**.

Стек: **PHP 8**, **MySQL**, без React — обычные страницы + немного JavaScript.

---

## 2. Какие файлы читать (и какие НЕ читать)

### Читай в таком порядке

| № | Файл | Зачем |
|---|------|--------|
| 1 | **`docs/GUIDE-MENYON.md`** (этот) | Как работать и что делать дальше |
| 2 | **`docs/IMPLEMENTATION-OPTIMUM.md`** | Твой **рабочий план** — задачи OPT-010, OPT-020… Отмечай `[x]` когда сделал |
| 3 | **`docs/PLAN-ETEO-SITE.md`** | Только если хочешь понять «почему так задумано» (можно бегло) |

### Не увязай в этом (пока не закончишь диплом)

| Файл | Почему |
|------|--------|
| **`docs/IMPLEMENTATION-ROADMAP.md`** | Полная версия на ~95 задач — для «большого продукта». Тебе почти всё лишнее |
| **`views/product/card.html`** | Старый макет, не подключён к сайту |

**Правило:** если AI предлагает сделать `/cases`, `/series`, админку, KK-переключатель — скажи: *«это вне scope, см. IMPLEMENTATION-OPTIMUM, раздел „Не делаем“»*.

---

## 3. Что уже готово (не ломай, но знай)

Это можно **показать на защите** и не переписывать с нуля:

| Что | Где | Как проверить |
|-----|-----|----------------|
| Роутер, вход | `index.php`, `router.php` | Запуск PHP-сервера (раздел 5) |
| Товары из БД | `controllers/ProductController.php` | `/product/1` |
| Конфигуратор + цена | `views/product/card.php`, `public/js/configurator.js` | Меняешь опции — цена меняется |
| Заказ в БД | `controllers/OrderController.php`, `POST /api/order/create` | Оформить заказ → `/order/success/N` |
| База + демо-товары | `sql/schema.sql` | ETEO-D1, ETEO-M3 |
| Миграции контента | `sql/migrate.sh`, `sql/migrations/*` | Таблица `leads`, FAQ, кейсы в БД (UI можешь не всё тянуть) |

**Твоя зона ответственности сейчас:** внешний вид сайта (layout), главная, каталог, форма КП, чтобы всё выглядело как **один продукт**, а не три разных черновика.

---

## 4. Карта папок (куда лезть руками)

```
furniture_platform/
├── index.php              ← маршруты (какой URL → какая страница)
├── config/config.php      ← настройки БД (обычно не трогать)
├── controllers/           ← PHP-логика (Product, Order, потом Lead)
├── views/                 ← HTML страницы (тут много твоей работы)
│   ├── layouts/main.php   ← общая обёртка (создашь в O2)
│   ├── partials/          ← шапка, подвал, форма
│   ├── home.php           ← главная
│   └── product/card.php   ← конфигуратор (уже есть)
├── public/css/site.css    ← светлый дизайн сайта (создашь)
├── public/css/configurator.css  ← тёмный конфигуратор (не ломай)
├── public/js/site.js      ← меню, FAQ, форма КП
├── public/js/configurator.js    ← конфигуратор (не ломай)
├── sql/                   ← база (уже настроена)
└── docs/                  ← планы и этот гайд
```

**Запомни:** страница = `views/...` + данные из `controllers/...` + стили из `public/`.

> **Важно:** весь код — в **корне** репозитория (`furniture_platform/`). Папки `shop/` в репо нет (была ошибочная копия — удалена). Не создавай дубликаты проекта.

---

## 5. Как запустить у себя на Windows

Нужно установить один раз:

| Программа | Откуда взять |
|-----------|----------------|
| **PHP 8.2+** | [windows.php.net](https://windows.php.net/download/) или `winget install PHP.PHP` |
| **MySQL 8** | [MySQL Installer](https://dev.mysql.com/downloads/installer/) или XAMPP (только MySQL) |
| **Терминал** | PowerShell или **Git Bash** (удобно для `migrate.sh`) |

При установке MySQL запомни пароль `root` (или оставь пустым для учебки — тогда в командах без `-p`).

Добавь в PATH (если не добавилось само): папки с `php.exe` и `mysql.exe` (часто `C:\Program Files\MySQL\MySQL Server 8.4\bin`).

**Если ошибка «выполнение сценариев отключено»** — не меняй политику навсегда, используй один из вариантов:

```powershell
# только в этом окне PowerShell:
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass
.\scripts\check-env.ps1
.\start-dev.ps1
```

Или двойной клик / из CMD (без политики):

```cmd
scripts\check-env.cmd
start-dev.cmd
```

---

### Каждый раз перед работой

**1.** Открой **PowerShell** или **CMD** и перейди в проект:

```powershell
cd C:\Users\ТВОЙ_ЮЗЕР\Downloads\furniture_platform
```

(подставь свой путь; в Cursor: ПКМ по папке проекта → «Открыть в терминале».)

**2.** Запусти MySQL (служба Windows):

```powershell
# Имя службы может быть MySQL80 или MySQL — смотри в services.msc
net start MySQL80
```

Либо: `Win + R` → `services.msc` → служба **MySQL80** → Запустить.

**3.** База — **первый раз** или после свежего клона:

**CMD** (из папки проекта):

```cmd
mysql -u root -p < sql\schema.sql
```

Если у `root` **нет пароля**:

```cmd
mysql -u root < sql\schema.sql
```

Миграции (таблица `leads`, seed и т.д.) — **один из вариантов**:

- **Git Bash** (если установлен Git for Windows):

```bash
./sql/migrate.sh
```

- **CMD** (по очереди все файлы из `sql\migrations\`):

```cmd
mysql -u root furniture_platform < sql\migrations\001_content_tables.sql
mysql -u root furniture_platform < sql\migrations\002_alter_products.sql
mysql -u root furniture_platform < sql\migrations\003_seed_content.sql
```

(если есть пароль — после `-u root` добавь `-p` и введи пароль.)

**4.** Запусти PHP-сервер.

**PowerShell:**

```powershell
$env:APP_DEBUG = "true"
$env:APP_URL = "http://localhost:8080"
$env:SESSION_SECURE = "false"
$env:DB_HOST = "127.0.0.1"
$env:DB_USER = "root"
$env:DB_PASSWORD = ""   # или твой пароль root
$env:DB_NAME = "furniture_platform"

php -S localhost:8080 router.php
```

**CMD:**

```cmd
set APP_DEBUG=true
set APP_URL=http://localhost:8080
set SESSION_SECURE=false
set DB_HOST=127.0.0.1
set DB_USER=root
set DB_PASSWORD=
set DB_NAME=furniture_platform

php -S localhost:8080 router.php
```

Окно не закрывай — пока оно открыто, сайт работает.

> **Mac / Linux / Git Bash** — скрипты в корне и `scripts/`:
>
> ```bash
> cd /path/to/shop   # или furniture_platform
> chmod +x scripts/check-env.sh scripts/dev.sh start-dev.sh   # один раз
> ./scripts/check-env.sh   # диагностика PHP, pdo_mysql, MySQL
> ./start-dev.sh           # то же что ./scripts/dev.sh
> ```
>
> На Windows в **Git Bash** те же команды (если `php` и `mysql` в PATH).

---

### Браузер и проверка

Открой: **http://localhost:8080**

Конфигуратор: **http://localhost:8080/product/1**

Если белый экран или **500** — смотри текст в том же окне PowerShell/CMD. Скрин + ошибка → в чат Cursor.

**Проверка MySQL** (лиды/заказы после тестов):

```cmd
mysql -u root -e "SELECT * FROM furniture_platform.leads ORDER BY id DESC LIMIT 3"
mysql -u root -e "SELECT * FROM furniture_platform.orders ORDER BY id DESC LIMIT 3"
```

(с паролем: `mysql -u root -p -e "..."`)

---

### Частые проблемы на Windows

| Проблема | Решение |
|----------|---------|
| `'php' is not recognized` | PHP не в PATH — переустанови с галочкой PATH или укажи полный путь: `"C:\php\php.exe" -S ...` |
| `'mysql' is not recognized` | Добавь `bin` MySQL в PATH или вызывай с полным путём |
| `Access denied for user 'root'` | В `DB_PASSWORD` укажи свой пароль; в CMD: `set DB_PASSWORD=твой_пароль` |
| Порт 8080 занят | Закрой другой сервер или: `php -S localhost:8888 router.php` и открой :8888 |
| `migrate.sh` не запускается | Используй Git Bash или три команды `mysql ... < sql\migrations\...` из пункта 3 |

---

## 6. Твой маршрут до защиты (пакеты)

Делай **по порядку**. После каждого пакета — прогони **мини-проверку** (раздел 8).

| Пакет | Задачи в OPTIMUM | Смысл | Оценка времени |
|-------|------------------|--------|----------------|
| ~~O1~~ | ✅ уже сделано | База, `leads`, seed | — |
| **O2** | OPT-010 … 016 | Шапка, подвал, `site.css`, 404 с дизайном | 1–2 дня |
| **O3** | OPT-020 … 025 | Нормальная главная (hero, блоки, FAQ) | 1 день |
| **O4** | OPT-030 … 033 | `/catalog`, каталог в общем стиле | 0.5–1 день |
| **O5** | OPT-040 … 044 | Форма КП + сохранение в `leads` | 0.5–1 день |
| **O6** | OPT-050 … 052 | Полировка + чеклист для защиты | 0.5 дня |

**Сейчас начинай с:** **ЧАТ 1** → промпт **O2** в **разделе 7**.

---

## 7. Промпты по чатам (копируй и вставляй)

**Главное правило:** один **пакет = один новый чат** в Cursor. Не пихай O2+O3+O4 в один чат — AI начнёт путаться и ломать конфигуратор.

**Перед ЛЮБЫМ чатом:**
1. `git pull origin main`
2. Запусти сервер (раздел 5), окно не закрывай
3. Создай **New Chat** (новый чат)

**После КАЖДОГО пакета:**
1. Проверь в браузере (раздел 8)
2. `git add .` → `git commit -m "O2: layout"` → `git push origin main`

---

### Шпаргалка: какой чат — что делать

| Чат | Пакет | Что получишь | Новый чат? |
|-----|-------|--------------|------------|
| **0** | Подготовка | Убедиться что проект живой | Да |
| **1** | O2 | Шапка, подвал, site.css, 404 | Да |
| **2** | O3 | Нормальная главная | Да |
| **3** | O4 | /catalog + меню на товаре | Да |
| **4** | O5 | Форма КП + API | Да |
| **5** | O6 | Финал + QA | Да |
| **SOS** | Починка | Если что-то сломалось | Да, отдельно |

---

### Что прикреплять через `@` в каждом чате

| Чат | Обязательно `@` | Иногда `@` |
|-----|-----------------|------------|
| 0 | — | — |
| 1 (O2) | `docs/IMPLEMENTATION-OPTIMUM.md`, `docs/GUIDE-MENYON.md` | `index.php`, `views/errors/404.php` |
| 2 (O3) | `docs/IMPLEMENTATION-OPTIMUM.md` | `views/home.php`, `views/layouts/main.php` |
| 3 (O4) | `docs/IMPLEMENTATION-OPTIMUM.md` | `index.php`, `controllers/ProductController.php` |
| 4 (O5) | `docs/IMPLEMENTATION-OPTIMUM.md` | `controllers/OrderController.php` (образец CSRF) |
| 5 (O6) | `docs/IMPLEMENTATION-OPTIMUM.md` | — |
| SOS | Файл с ошибкой + `docs/IMPLEMENTATION-OPTIMUM.md` | — |

**Не прикрепляй:** `docs/IMPLEMENTATION-ROADMAP.md` — там лишние 95 задач, AI уедет не туда.

**Запрет для всех чатов** (вставляй в каждый промпт):

```text
ЗАПРЕЩЕНО: папка shop/, Laravel, React, /cases, /series, админка, KK-язык, ROADMAP.
Работай только в КОРНЕ репозитория.
Не ломай: /product/1, configurator.js, OrderController, POST /api/order/create.
```

---

## ЧАТ 0 — «Проект вообще запускается?»

**Когда:** один раз перед первым промптом, или после `git pull`.

**Делай руками (не AI):**

```cmd
git pull origin main
net start MySQL80
mysql -u root < sql\schema.sql
mysql -u root furniture_platform < sql\migrations\001_content_tables.sql
mysql -u root furniture_platform < sql\migrations\002_alter_products.sql
mysql -u root furniture_platform < sql\migrations\003_seed_content.sql
```

PowerShell — запуск сервера (раздел 5, пункт 4).

**Проверь в браузере:**
- http://localhost:8080/product/1 — конфигуратор, цена меняется
- Оформи тестовый заказ — должна открыться success

**Если тут не работает — не иди в ЧАТ 1.** Скинь ошибку наставнику или в **ЧАТ SOS**.

---

## ЧАТ 1 — Пакет O2 (layout, шапка, подвал)

**@ контекст:** `docs/IMPLEMENTATION-OPTIMUM.md`, `docs/GUIDE-MENYON.md`

**Скопируй целиком:**

```text
Реализуй пакет O2 из docs/IMPLEMENTATION-OPTIMUM.md — задачи OPT-010, OPT-011, OPT-012, OPT-013, OPT-014, OPT-015, OPT-016.

ЗАПРЕЩЕНО: папка shop/, Laravel, React, /cases, /series, админка, KK-язык, ROADMAP.
Работай только в КОРНЕ репозитория.
Не ломай: /product/1, configurator.js, OrderController, POST /api/order/create.

Нужно создать:
- public/css/site.css (светлая тема)
- views/helpers.php
- includes/view.php + функция render()
- views/layouts/main.php
- views/partials/header.php, footer.php
- public/js/site.js (burger, FAQ-пока заглушка ok, якоря)
- перевести views/errors/404.php и 500.php на layout

После работы:
1. Отметь [x] OPT-010…016 в docs/IMPLEMENTATION-OPTIMUM.md
2. Обнови «Текущий статус» → следующая OPT-020
3. Список изменённых файлов
4. Что проверить в браузере
```

**После ответа AI — проверь (раздел 8 → «После O2»).**

**Коммит:**
```cmd
git add .
git commit -m "O2: layout, header, footer, site.css"
git push origin main
```

**Если AI сломал /product/1 — сразу ЧАТ SOS (промпт внизу).**

---

## ЧАТ 2 — Пакет O3 (главная страница)

**Новый чат.** **@:** `docs/IMPLEMENTATION-OPTIMUM.md`, `views/layouts/main.php`

```text
Реализуй пакет O3 из docs/IMPLEMENTATION-OPTIMUM.md — задачи OPT-020, OPT-021, OPT-022, OPT-023, OPT-024, OPT-025.

ЗАПРЕЩЕНО: shop/, Laravel, /cases URL, /series, админка, ROADMAP.
Не ломай конфигуратор и layout из O2.

Секции в views/partials/sections/:
- hero.php (3 кнопки: Каталог, Запросить КП → #lead-form, Конфигуратор → /product/1)
- directions.php (3 карточки → /catalog/1, /catalog/2, /catalog/3)
- advantages.php (4 тезиса, статика)
- cases-static.php (2–3 карточки БЕЗ отдельных страниц)
- faq.php (4 вопроса, аккордеон — site.js уже есть)
- lead.php — пока заглушка «форма будет в O5» или пустой блок #lead-form

Пересобери views/home.php через render() и секции.
Обнови маршрут GET / в index.php если нужно.

После: [x] OPT-020…025, обнови статус, список файлов, проверка в браузере.
```

**Проверь:** раздел 8 → «После O3».

**Коммит:** `git commit -m "O3: marketing home page"`

---

## ЧАТ 3 — Пакет O4 (каталог)

**Новый чат.** **@:** `docs/IMPLEMENTATION-OPTIMUM.md`, `index.php`, `controllers/ProductController.php`

```text
Реализуй пакет O4 из docs/IMPLEMENTATION-OPTIMUM.md — OPT-030, OPT-031, OPT-032, OPT-033.

ЗАПРЕЩЕНО: shop/, slug-URL, /series, ROADMAP.
Не ломай конфигуратор.

Нужно:
- GET /catalog → views/catalog/index.php (список категорий из БД)
- GET /catalog/{id} → views/catalog/category.php с layout (замени старый catalog.php)
- views/product/card.php — общий header/footer (site.css + configurator.css)
- public/images/placeholder.webp или jpg + без битых картинок

После: [x] OPT-030…033, статус, файлы, проверка.
```

**Проверь:** раздел 8 → «После O4».

**Коммит:** `git commit -m "O4: catalog pages and product layout"`

---

## ЧАТ 4 — Пакет O5 (форма КП + API)

**Новый чат.** **@:** `docs/IMPLEMENTATION-OPTIMUM.md`, `controllers/OrderController.php` (чтобы AI скопировал CSRF)

```text
Реализуй пакет O5 из docs/IMPLEMENTATION-OPTIMUM.md — OPT-040, OPT-041, OPT-042, OPT-043, OPT-044.

ЗАПРЕЩЕНО: shop/, email-рассылка, ROADMAP.
Не ломай POST /api/order/create.

Нужно:
- controllers/LeadController.php
- POST /api/lead/create в index.php
- views/partials/lead-form.php
- public/js/site.js — отправка формы (fetch CSRF → POST JSON)
- форма на главной (секция lead) и компактная на /product/1
- INSERT в таблицу leads (уже есть в БД)

CSRF — как в OrderController.

После: [x] OPT-040…044, статус, файлы, curl или браузер-проверка.
```

**Проверь:** раздел 8 → «После O5».

**Коммит:** `git commit -m "O5: lead form and API"`

---

## ЧАТ 5 — Пакет O6 (финал перед защитой)

**Новый чат.** **@:** `docs/IMPLEMENTATION-OPTIMUM.md`

```text
Реализуй пакет O6 из docs/IMPLEMENTATION-OPTIMUM.md — OPT-050, OPT-051, OPT-052.

ЗАПРЕЩЕНО: shop/, ROADMAP, новые фичи вне OPTIMUM.

Нужно:
- views/order/success.php — через общий layout
- docs/QA-DIPLOMA.md — чеклист URL + сценарий защиты + curl для API
- README.md в корне — как запустить на Windows (PHP, MySQL, php -S)
- отметить [x] все пакеты O2–O6 в IMPLEMENTATION-OPTIMUM.md

После: полный список что проверить перед защитой.
```

**Проверь:** раздел 8 → «Перед защитой» + пройди `docs/QA-DIPLOMA.md`.

**Коммит:** `git commit -m "O6: polish, QA checklist, README"`

---

## ЧАТ SOS — «Всё сломалось»

**Новый чат.** **@:** файл с ошибкой + `docs/IMPLEMENTATION-OPTIMUM.md`

**Если сломался конфигуратор / заказ:**

```text
Сломался /product/1 или заказ после последних изменений.

Ошибка (из терминала или браузера):
<<<
ВСТАВЬ СЮДА ТЕКСТ ОШИБКИ
>>>

Почини минимальным diff. Не трогай файлы вне проблемы.
Не делай рефакторинг. Не трогай O3/O4 если чинишь O2.
Проверь что /product/1 и POST /api/order/create снова работают.
```

**Если AI налепил лишнее (админка, shop/, 20 файлов):**

```text
Откати лишнее. Нужно было только OPT-XXX из docs/IMPLEMENTATION-OPTIMUM.md.
Удали всё что не в списке файлов задачи OPT-XXX.
Верни работоспособность /product/1.
```

**Если белый экран 500:**

```text
500 на http://localhost:8080/ после твоих изменений.

Лог PHP:
<<<
ВСТАВЬ ИЗ ТЕРМИНАЛА
>>>

Исправь. Минимальный diff.
```

---

## Если AI сделал криво — уменьши задачу

Вместо целого пакета — **одна задача, новый чат:**

```text
Реализуй ТОЛЬКО OPT-013 из docs/IMPLEMENTATION-OPTIMUM.md.
Только views/partials/header.php и стили в site.css для header.
Не трогай другие файлы.
```

Такие номера: OPT-010, OPT-012, OPT-013, OPT-020, OPT-030, OPT-040… — смотри в `IMPLEMENTATION-OPTIMUM.md`.

---

## Чего НЕ делать с AI (напоминание)

| Плохо | Хорошо |
|-------|--------|
| Один чат на весь проект | Чат 1 = O2, чат 2 = O3… |
| «Сделай как eteo.ru 1 в 1» | «Пакет O3 по OPTIMUM» |
| «Залей в папку shop» | «Только корень репо» |
| Принять код, не открыв браузер | Проверил → commit → push |
| Upload files на GitHub | `git commit` + `git push` |

---

## 8. Мини-проверки после каждого пакета

### После O2 (layout)

- [ ] http://localhost:8080/ — есть шапка и подвал
- [ ] http://localhost:8080/zzzz — 404, но **с шапкой**, не голый текст
- [ ] http://localhost:8080/product/1 — конфигуратор **всё ещё работает**

### После O3 (главная)

- [ ] Главная не «просто список товаров» — есть hero, блоки, FAQ
- [ ] Кнопка «Каталог» ведёт на `/catalog`
- [ ] «Запросить КП» скроллит к форме (`#lead-form`)

### После O4 (каталог)

- [ ] http://localhost:8080/catalog — список категорий
- [ ] http://localhost:8080/catalog/1 — товары ETEO-D1 / ETEO-M3
- [ ] «Настроить» → `/product/1`

### После O5 (лиды)

- [ ] Отправил форму на главной — без ошибки внизу формы
- [ ] В терминале:  
  `mysql -u root furniture_platform -e "SELECT * FROM leads ORDER BY id DESC LIMIT 1"`  
  — видна твоя заявка

### Перед защитой (всё вместе)

- [ ] Полный сценарий 2–3 мин (раздел 9) прошёл без сюрпризов
- [ ] Можешь своими словами объяснить: что такое CSRF, зачем `orders` и `leads`

---

## 9. Сценарий на защите (выучи руками)

Делай **медленно**, комиссия должна видеть экран:

1. **Главная** — «Это лендинг производителя диспетчерской мебели, референс по UX — eteo.ru, но у нас свой акцент — онлайн-конфигуратор».
2. **Каталог** → категория «Диспетчерская мебель».
3. **Товар ETEO-D1** — меняешь 1–2 опции, цена пересчитывается.
4. **Оформить заказ** — заполняешь модалку, отправляешь.
5. **Страница успеха** с номером заказа.
6. *(Если успели O5)* **Форма КП** на главной — «второй канал заявок, пишет в таблицу `leads`».

**Если спросят «чем отличается от eteo.ru»:**

> У них заявка менеджеру и КП вручную. У нас покупатель сам собирает конфигурацию, видит цену и может оформить заказ в системе; данные в MySQL, API с защитой CSRF.

**Если спросят «что ты делал лично»:**

> Интеграция интерфейса: общий layout, главная, каталог, форма лидов, связка с существующим конфигуратором и API. База и миграции — с наставником / по командному плану. *(Говори честно, как у вас было.)*

---

## 10. Что понять для ответов на вопросы (шпаргалка)

| Тема | Короткий ответ |
|------|----------------|
| **Архитектура** | Front controller: все запросы в `index.php`, дальше контроллер → view |
| **Безопасность** | Prepared statements (PDO), CSRF на POST, `htmlspecialchars` в шаблонах |
| **Заказ** | `orders` + `order_items`, цена пересчитывается на сервере, не только в JS |
| **Лид (КП)** | Отдельная таблица `leads`, не путать с заказом |
| **Конфигуратор** | Опции в `product_options`, JS для UX, сервер проверяет цену при заказе |

Папки `controllers/OrderController.php` и `ProductController.php` — **открой хотя бы раз** и пробеги глазами названия методов перед защитой.

---

## 11. Если что-то сломалось

| Симптом | Что сделать |
|---------|-------------|
| `Connection refused` MySQL | Windows: `net start MySQL80` или services.msc → MySQL |
| `Unknown database` | `mysql -u root < sql/schema.sql` && `./sql/migrate.sh` |
| 500 на всех страницах | Смотри окно PowerShell/CMD, где крутится `php -S`, скинь ошибку в Cursor |
| Конфигуратор не считает цену | Не трогай `configurator.js` / `card.php` без нужды; откати git |
| AI налепил лишнего | Новый чат: «откати X, сделай только OPT-YYY» |

**Git после каждого пакета:**

```cmd
git pull origin main
git add .
git commit -m "O2: layout"
git push origin main
```

Подставляй своё сообщение: `O3: home`, `O4: catalog`, `O5: leads`, `O6: polish`.

---

## 12. Чеклист «я готов к защите»

Распечатай или держи открытым:

- [ ] Запускаю проект без подсказок (раздел 5, Windows + браузер)
- [ ] Прошёл демо-сценарий (раздел 9) 2 раза подряд без ошибок
- [ ] Знаю, где `index.php`, `ProductController`, `OrderController`, `views/home.php`
- [ ] В OPTIMUM все пакеты O2–O6 отмечены `[x]` (или объясняю, что не успел)
- [ ] Есть `docs/QA-DIPLOMA.md` (создаётся в OPT-051) — прогнал пункты
- [ ] Понимаю разницу заказ vs заявка на КП

---

## 13. Следующее действие (прямо сейчас)

1. **ЧАТ 0** — `git pull`, запусти сервер, проверь http://localhost:8080/product/1  
2. **ЧАТ 1** — скопируй промпт из раздела 7 «ЧАТ 1 — O2»  
3. Проверь браузер → **commit + push**  
4. **ЧАТ 2** — новый чат, промпт O3  
5. И так до **ЧАТ 5**

**Ты сейчас здесь:** O1 ✅ → начинай **ЧАТ 1 (O2)**.

---

*Версия: 2026-05-27 · промпты по чатам для дипломной ветки «Оптимум»*
