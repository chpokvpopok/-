-- Кресло L2: только кресло, базовая цена ~200 000 ₸

USE furniture_platform;

UPDATE products SET
    name_ru = 'Кресло',
    name_kk = 'Кресло',
    description_ru = 'Компактное кресло для гостиной или спальни. Выберите обивку и при необходимости пуф.',
    description_kk = 'Гостинаяға арналған ыңғайлы кресло.',
    base_price = 198000.00
WHERE sku = 'LIVING-CHAIR-02';

SET @p4 = (SELECT id FROM products WHERE sku = 'LIVING-CHAIR-02' LIMIT 1);
DELETE FROM product_options WHERE product_id = @p4;

INSERT INTO product_options (product_id, option_name_ru, option_name_kk, option_type, option_value_ru, option_value_kk, option_group, price_modifier, sort_order)
SELECT @p4, 'Обивка', 'Жабын', 'select', 'Рогожка', 'Сыбыз', 'material', 0, 1
UNION ALL SELECT @p4, 'Обивка', 'Жабын', 'select', 'Велюр', 'Велюр', 'material', 15000, 2
UNION ALL SELECT @p4, 'Обивка', 'Жабын', 'select', 'Микрофибра', 'Микрофибра', 'material', 28000, 3
UNION ALL SELECT @p4, 'Подголовник', 'Басқыш', 'checkbox', NULL, NULL, 'extras', 12000, 4
UNION ALL SELECT @p4, 'Пуф-куб', 'Пуф', 'checkbox', NULL, NULL, 'extras', 22000, 5;

SELECT '✅ Кресло L2 обновлено' AS status;
