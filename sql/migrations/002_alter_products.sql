-- =============================================================
-- Миграция 002: расширение таблицы products (series_id, slug)
-- Идемпотентно: проверка через INFORMATION_SCHEMA
-- Без кириллицы в динамических ALTER (совместимость Windows/mysql)
-- =============================================================

USE furniture_platform;

SET @db = DATABASE();

-- series_id
SET @n = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'products' AND COLUMN_NAME = 'series_id'
);
SET @q = IF(@n = 0,
    'ALTER TABLE products ADD COLUMN series_id INT UNSIGNED NULL AFTER category_id',
    'SELECT 1');
PREPARE st FROM @q;
EXECUTE st;
DEALLOCATE PREPARE st;

-- slug
SET @n = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'products' AND COLUMN_NAME = 'slug'
);
SET @q = IF(@n = 0,
    'ALTER TABLE products ADD COLUMN slug VARCHAR(220) NULL AFTER sku',
    'SELECT 1');
PREPARE st FROM @q;
EXECUTE st;
DEALLOCATE PREPARE st;

-- FK (только если есть product_series из миграции 001)
SET @ps = (
    SELECT COUNT(*) FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'product_series'
);
SET @fk = (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'products'
      AND CONSTRAINT_NAME = 'fk_products_series' AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);
SET @q = IF(@ps > 0 AND @fk = 0,
    'ALTER TABLE products ADD CONSTRAINT fk_products_series FOREIGN KEY (series_id) REFERENCES product_series(id) ON UPDATE CASCADE ON DELETE SET NULL',
    'SELECT 1');
PREPARE st FROM @q;
EXECUTE st;
DEALLOCATE PREPARE st;

-- INDEX idx_products_series
SET @ix = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'products' AND INDEX_NAME = 'idx_products_series'
);
SET @q = IF(@ix = 0,
    'ALTER TABLE products ADD INDEX idx_products_series (series_id)',
    'SELECT 1');
PREPARE st FROM @q;
EXECUTE st;
DEALLOCATE PREPARE st;
