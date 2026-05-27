-- =============================================================
-- СХЕМА БАЗЫ ДАННЫХ: Веб-платформа мебельного производства
-- Кодировка: utf8mb4_unicode_ci (полная поддержка Unicode / эмодзи)
-- Движок: InnoDB (поддержка внешних ключей, транзакций ACID)
-- =============================================================

CREATE DATABASE IF NOT EXISTS furniture_platform
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE furniture_platform;

-- -------------------------------------------------------------
-- Таблица пользователей системы
-- Роли: client — покупатель, manager — менеджер заказов, admin — суперадмин
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    name          VARCHAR(150)     NOT NULL COMMENT 'Полное имя пользователя',
    email         VARCHAR(255)     NOT NULL COMMENT 'Уникальный e-mail (логин)',
    password_hash VARCHAR(255)     NOT NULL COMMENT 'bcrypt-хеш пароля (cost >= 12)',
    role          ENUM('client','manager','admin')
                                   NOT NULL DEFAULT 'client',
    created_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    INDEX idx_users_role (role)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Зарегистрированные пользователи платформы';


-- -------------------------------------------------------------
-- Таблица категорий товаров (двуязычная: RU + KK)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id       INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    name_ru  VARCHAR(200)  NOT NULL COMMENT 'Название на русском',
    name_kk  VARCHAR(200)  NOT NULL COMMENT 'Название на казахском',
    slug     VARCHAR(220)  NOT NULL COMMENT 'URL-совместимый идентификатор (латиница)',

    PRIMARY KEY (id),
    UNIQUE KEY uq_categories_slug (slug),
    INDEX idx_categories_name_ru (name_ru)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Иерархия категорий мебельного каталога';


-- -------------------------------------------------------------
-- Таблица товаров
-- Внешний ключ: category_id → categories.id (CASCADE DELETE для
-- автоматической очистки сирот при удалении категории)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
    id              INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    category_id     INT UNSIGNED      NOT NULL,
    name_ru         VARCHAR(300)      NOT NULL COMMENT 'Наименование (RU)',
    name_kk         VARCHAR(300)      NOT NULL COMMENT 'Наименование (KK)',
    description_ru  TEXT                       COMMENT 'Полное описание (RU)',
    description_kk  TEXT                       COMMENT 'Полное описание (KK)',
    base_price      DECIMAL(12, 2)    NOT NULL DEFAULT 0.00
                    COMMENT 'Базовая цена базовой конфигурации, тенге',
    image_preview   VARCHAR(500)               COMMENT 'Путь к превью-изображению',
    active          TINYINT(1)        NOT NULL DEFAULT 1
                    COMMENT '1 — опубликован, 0 — скрыт',
    sku             VARCHAR(100)      NOT NULL COMMENT 'Артикул (Stock Keeping Unit)',
    created_at      DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_products_sku (sku),
    INDEX idx_products_category (category_id),
    INDEX idx_products_active  (active),
    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id)
        REFERENCES categories (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT   -- запрет удаления категории при наличии товаров
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Каталог мебельной продукции';


-- -------------------------------------------------------------
-- Таблица конфигурационных опций товаров
-- Примеры опций: Материал столешницы (select),
--                LED-подсветка (checkbox), Блок розеток (checkbox)
-- price_modifier — надбавка к base_price (может быть отрицательной)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_options (
    id              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    product_id      INT UNSIGNED     NOT NULL,
    option_name_ru  VARCHAR(200)     NOT NULL COMMENT 'Название опции (RU)',
    option_name_kk  VARCHAR(200)     NOT NULL COMMENT 'Название опции (KK)',
    option_type     ENUM('select','checkbox')
                                     NOT NULL DEFAULT 'select',
    option_value_ru VARCHAR(200)              COMMENT 'Значение опции — для select (RU)',
    option_value_kk VARCHAR(200)              COMMENT 'Значение опции — для select (KK)',
    option_group    VARCHAR(100)              COMMENT 'Группировка опций одного select',
    price_modifier  DECIMAL(12, 2)   NOT NULL DEFAULT 0.00
                    COMMENT 'Прибавка к итоговой цене, тенге',
    sort_order      SMALLINT UNSIGNED NOT NULL DEFAULT 0,

    PRIMARY KEY (id),
    INDEX idx_po_product (product_id),
    INDEX idx_po_group   (product_id, option_group),
    CONSTRAINT fk_product_options_product
        FOREIGN KEY (product_id)
        REFERENCES products (id)
        ON UPDATE CASCADE
        ON DELETE CASCADE   -- опции удаляются вместе с товаром
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Конфигурационные опции товаров (параметры кастомизации)';


-- -------------------------------------------------------------
-- Таблица заказов
-- delivery_info хранится как JSON-строка:
--   {"address":"...", "city":"...", "phone":"...", "comment":"..."}
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    user_id       INT UNSIGNED              COMMENT 'NULL — гостевой заказ',
    total_price   DECIMAL(14, 2)   NOT NULL DEFAULT 0.00
                  COMMENT 'Итоговая сумма заказа, тенге',
    status        ENUM('new','processing','production','ready','shipped','done')
                                   NOT NULL DEFAULT 'new',
    delivery_info JSON                      COMMENT 'Контактные данные и адрес доставки',
    created_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP
                                   ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_orders_user   (user_id),
    INDEX idx_orders_status (status),
    INDEX idx_orders_date   (created_at),
    CONSTRAINT fk_orders_user
        FOREIGN KEY (user_id)
        REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE SET NULL  -- заказ сохраняется при удалении аккаунта
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Заказы покупателей';


-- -------------------------------------------------------------
-- Таблица позиций заказа (строки корзины)
-- selected_options_json — снимок выбранных опций на момент заказа:
--   [{"option_id":3,"option_name_ru":"HPL-пластик","price_modifier":15000}, ...]
-- Снимок хранится намеренно — цена опции может измениться, но
-- зафиксированный заказ должен сохранять историческое значение.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_items (
    id                    INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    order_id              INT UNSIGNED    NOT NULL,
    product_id            INT UNSIGNED    NOT NULL,
    selected_options_json JSON                     COMMENT 'Снимок выбранных опций',
    quantity              SMALLINT UNSIGNED
                                          NOT NULL DEFAULT 1,
    price                 DECIMAL(12, 2)  NOT NULL DEFAULT 0.00
                          COMMENT 'Цена единицы с учётом опций на момент заказа',

    PRIMARY KEY (id),
    INDEX idx_oi_order   (order_id),
    INDEX idx_oi_product (product_id),
    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id)
        REFERENCES orders (id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product
        FOREIGN KEY (product_id)
        REFERENCES products (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Позиции (строки) заказов';


-- =============================================================
-- ДЕМО-ДАННЫЕ: категории и диспетчерская мебель
-- =============================================================

INSERT INTO categories (name_ru, name_kk, slug) VALUES
('Диспетчерская мебель',   'Диспетчерлік жиһаз',    'dispetcherskaya'),
('Технологическая мебель', 'Технологиялық жиһаз',   'tekhnologicheskaya'),
('Модульные системы',      'Модульдік жүйелер',      'modulnye-sistemy');

INSERT INTO products
    (category_id, name_ru, name_kk, description_ru, description_kk,
     base_price, sku, active)
VALUES
(
    1,
    'Диспетчерский пульт ETEO-D1',
    'ETEO-D1 диспетчерлік пульті',
    'Профессиональный диспетчерский пульт с эргономичной рабочей поверхностью, '
    'встроенным кабельным каналом и опциональными системами климат-контроля. '
    'Предназначен для операционных центров, ситуационных комнат и центров управления.',
    'Кабелдік арна, климат бақылау жүйесі және монитор кронштейндерімен '
    'жабдықталған кәсіби диспетчерлік пульт.',
    850000.00,
    'ETEO-D1-BASE',
    1
),
(
    1,
    'Модульный пульт ETEO-M3',
    'ETEO-M3 модульдік пульті',
    'Масштабируемый модульный пульт для крупных операционных центров. '
    'Возможность наращивания до 6 рабочих мест без потери эргономики.',
    'Үлкен операциялық орталықтарға арналған модульдік пульт. '
    '6 жұмыс орнына дейін кеңейту мүмкіндігі.',
    1250000.00,
    'ETEO-M3-BASE',
    1
);

-- Опции для товара id=1 (ETEO-D1)
INSERT INTO product_options
    (product_id, option_name_ru, option_name_kk, option_type,
     option_value_ru, option_value_kk, option_group, price_modifier, sort_order)
VALUES
-- Группа: Материал столешницы (select — взаимоисключающий выбор)
(1, 'Материал столешницы', 'Үстел материалы', 'select',
 'ЛДСП стандарт', 'ЛДСП стандарт', 'material', 0.00, 1),
(1, 'Материал столешницы', 'Үстел материалы', 'select',
 'HPL-пластик', 'HPL-пластик', 'material', 45000.00, 2),
(1, 'Материал столешницы', 'Үстел материалы', 'select',
 'Натуральный шпон', 'Натурал шпон', 'material', 90000.00, 3),

-- Группа: Конфигурация (select)
(1, 'Конфигурация', 'Конфигурация', 'select',
 'Прямая', 'Тура', 'config', 0.00, 4),
(1, 'Конфигурация', 'Конфигурация', 'select',
 'Радиусная', 'Радиустық', 'config', 60000.00, 5),

-- Дополнительные опции (checkbox — независимый выбор)
(1, 'Блок розеток и USB', 'Розетка және USB блогы',
 'checkbox', NULL, NULL, 'extras', 18000.00, 6),
(1, 'Система климат-контроля', 'Климат бақылау жүйесі',
 'checkbox', NULL, NULL, 'extras', 75000.00, 7),
(1, 'Кронштейны для мониторов (3 шт.)', 'Монитор кронштейндері (3 дана)',
 'checkbox', NULL, NULL, 'extras', 36000.00, 8),
(1, 'LED-подсветка рабочей зоны', 'Жұмыс аймағының LED-жарықтандыруы',
 'checkbox', NULL, NULL, 'extras', 22000.00, 9),
(1, 'Встроенный кабельный канал', 'Кабельдік арна',
 'checkbox', NULL, NULL, 'extras', 12000.00, 10);
