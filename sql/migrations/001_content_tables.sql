-- =============================================================
-- Миграция 001: таблицы контента (pages, product_series, cases,
-- faq_items, leads, site_settings)
-- Идемпотентно: CREATE TABLE IF NOT EXISTS
-- =============================================================

USE furniture_platform;

-- -------------------------------------------------------------
-- Статические страницы и блоки контента
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pages (
    id          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    slug        VARCHAR(220)     NOT NULL COMMENT 'URL-идентификатор',
    type        ENUM('service','competence','advantage','static')
                                 NOT NULL,
    title_ru    VARCHAR(300)     NOT NULL,
    title_kk    VARCHAR(300)     NOT NULL,
    excerpt_ru  TEXT                      COMMENT 'Краткое описание (RU)',
    excerpt_kk  TEXT                      COMMENT 'Краткое описание (KK)',
    body_ru     MEDIUMTEXT                COMMENT 'Полный текст (RU)',
    body_kk     MEDIUMTEXT                COMMENT 'Полный текст (KK)',
    image       VARCHAR(500)              COMMENT 'Путь к изображению',
    sort_order  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    active      TINYINT(1)       NOT NULL DEFAULT 1,

    PRIMARY KEY (id),
    UNIQUE KEY uq_pages_slug (slug),
    INDEX idx_pages_type (type),
    INDEX idx_pages_active (active)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Статические страницы и блоки контента';

-- -------------------------------------------------------------
-- Серии продуктов (Van / One, Flow, Pulse)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_series (
    id             INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    slug           VARCHAR(220)     NOT NULL COMMENT 'URL-идентификатор серии',
    name_ru        VARCHAR(200)     NOT NULL,
    name_kk        VARCHAR(200)     NOT NULL,
    description_ru TEXT,
    description_kk TEXT,
    image          VARCHAR(500),
    sort_order     SMALLINT UNSIGNED NOT NULL DEFAULT 0,

    PRIMARY KEY (id),
    UNIQUE KEY uq_product_series_slug (slug)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Серии продуктов (ETEO One, Flow, Pulse)';

-- -------------------------------------------------------------
-- Портфолио / кейсы
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cases (
    id          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    slug        VARCHAR(220)     NOT NULL COMMENT 'URL-идентификатор кейса',
    client      VARCHAR(200)     NOT NULL COMMENT 'Название клиента',
    title_ru    VARCHAR(300)     NOT NULL,
    title_kk    VARCHAR(300)     NOT NULL,
    excerpt_ru  TEXT,
    excerpt_kk  TEXT,
    body_ru     MEDIUMTEXT,
    body_kk     MEDIUMTEXT,
    image       VARCHAR(500),
    sort_order  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    active      TINYINT(1)       NOT NULL DEFAULT 1,
    created_at  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_cases_slug (slug),
    INDEX idx_cases_active (active)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Портфолио реализованных проектов';

-- -------------------------------------------------------------
-- FAQ
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS faq_items (
    id          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    question_ru VARCHAR(500)     NOT NULL,
    question_kk VARCHAR(500)     NOT NULL,
    answer_ru   TEXT             NOT NULL,
    answer_kk   TEXT             NOT NULL,
    sort_order  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    active      TINYINT(1)       NOT NULL DEFAULT 1,

    PRIMARY KEY (id),
    INDEX idx_faq_sort (sort_order),
    INDEX idx_faq_active (active)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Часто задаваемые вопросы';

-- -------------------------------------------------------------
-- Заявки на КП (лиды)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS leads (
    id           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    email        VARCHAR(255)     NOT NULL,
    name         VARCHAR(150)     NOT NULL,
    phone        VARCHAR(50)      NOT NULL,
    organization VARCHAR(200),
    comment      TEXT,
    source       ENUM('home','cases','contacts','product')
                                  NOT NULL DEFAULT 'home',
    status       ENUM('new','contacted','done')
                                  NOT NULL DEFAULT 'new',
    created_at   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_leads_status (status),
    INDEX idx_leads_source (source),
    INDEX idx_leads_created (created_at)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Заявки на коммерческое предложение';

-- -------------------------------------------------------------
-- Настройки сайта (контакты, hero, соцсети)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS site_settings (
    setting_key VARCHAR(100) NOT NULL COMMENT 'Ключ настройки',
    value_ru    TEXT,
    value_kk    TEXT,

    PRIMARY KEY (setting_key)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Настройки сайта (key-value)';
