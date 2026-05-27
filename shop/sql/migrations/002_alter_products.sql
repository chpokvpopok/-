-- =============================================================
-- Миграция 002: расширение таблицы products (series_id, slug)
-- Идемпотентно: проверка через INFORMATION_SCHEMA
-- =============================================================

USE furniture_platform;

-- series_id
SET @col_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'products'
      AND COLUMN_NAME = 'series_id'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE products ADD COLUMN series_id INT UNSIGNED NULL COMMENT ''FK на product_series'' AFTER category_id',
    'SELECT ''series_id already exists'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- slug
SET @col_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'products'
      AND COLUMN_NAME = 'slug'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE products ADD COLUMN slug VARCHAR(220) NULL COMMENT ''URL-идентификатор товара'' AFTER sku',
    'SELECT ''slug already exists'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- FK fk_products_series
SET @fk_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'products'
      AND CONSTRAINT_NAME = 'fk_products_series'
      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);
SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE products ADD CONSTRAINT fk_products_series FOREIGN KEY (series_id) REFERENCES product_series(id) ON UPDATE CASCADE ON DELETE SET NULL',
    'SELECT ''fk_products_series already exists'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- INDEX idx_products_series
SET @idx_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'products'
      AND INDEX_NAME = 'idx_products_series'
);
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE products ADD INDEX idx_products_series (series_id)',
    'SELECT ''idx_products_series already exists'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
