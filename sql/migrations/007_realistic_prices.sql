-- Реалистичные цены в тенге (домашняя мебель, Казахстан, 2026)

USE furniture_platform;

UPDATE products SET base_price = 1189000.00 WHERE sku IN ('BEDROOM-SET-01', 'ETEO-D1-BASE');
UPDATE products SET base_price = 429000.00  WHERE sku = 'BEDROOM-BED-02';
UPDATE products SET base_price = 1790000.00 WHERE sku IN ('LIVING-ROOM-SET-01', 'ETEO-M3-BASE');
UPDATE products SET base_price = 249000.00  WHERE sku = 'LIVING-CHAIR-02';
UPDATE products SET base_price = 629000.00  WHERE sku = 'KITCHEN-DINING-01';
UPDATE products SET base_price = 489000.00  WHERE sku = 'HOME-OFFICE-01';
UPDATE products SET base_price = 219000.00  WHERE sku = 'ENTRYWAY-01';
UPDATE products SET base_price = 699000.00  WHERE sku = 'KIDS-ROOM-01';

SET @p1 = (SELECT id FROM products WHERE sku IN ('BEDROOM-SET-01', 'ETEO-D1-BASE') LIMIT 1);
SET @p2 = (SELECT id FROM products WHERE sku = 'BEDROOM-BED-02' LIMIT 1);
SET @p3 = (SELECT id FROM products WHERE sku IN ('LIVING-ROOM-SET-01', 'ETEO-M3-BASE') LIMIT 1);
SET @p4 = (SELECT id FROM products WHERE sku = 'LIVING-CHAIR-02' LIMIT 1);
SET @p5 = (SELECT id FROM products WHERE sku = 'KITCHEN-DINING-01' LIMIT 1);
SET @p6 = (SELECT id FROM products WHERE sku = 'HOME-OFFICE-01' LIMIT 1);
SET @p7 = (SELECT id FROM products WHERE sku = 'ENTRYWAY-01' LIMIT 1);
SET @p8 = (SELECT id FROM products WHERE sku = 'KIDS-ROOM-01' LIMIT 1);

DELETE FROM product_options WHERE product_id IN (@p1, @p2, @p3, @p4, @p5, @p6, @p7, @p8);

INSERT INTO product_options (product_id, option_name_ru, option_name_kk, option_type, option_value_ru, option_value_kk, option_group, price_modifier, sort_order)
SELECT @p1, 'Ткань изголовья', 'Жабын', 'select', 'Лён светлый', 'Зығыр', 'material', 0, 1
UNION ALL SELECT @p1, 'Ткань изголовья', 'Жабын', 'select', 'Велюр', 'Велюр', 'material', 52000, 2
UNION ALL SELECT @p1, 'Ткань изголовья', 'Жабын', 'select', 'Экокожа', 'Экожа', 'material', 118000, 3
UNION ALL SELECT @p1, 'Размер кровати', 'Өлшем', 'select', '160×200', '160×200', 'config', 0, 4
UNION ALL SELECT @p1, 'Размер кровати', 'Өлшем', 'select', '180×200', '180×200', 'config', 42000, 5
UNION ALL SELECT @p1, 'Размер кровати', 'Өлшем', 'select', '200×200', '200×200', 'config', 78000, 6
UNION ALL SELECT @p1, 'Ортопедический матрас', 'Матрас', 'checkbox', NULL, NULL, 'extras', 95000, 7
UNION ALL SELECT @p1, 'Подсветка изголовья', 'Жарық', 'checkbox', NULL, NULL, 'extras', 32000, 8
UNION ALL SELECT @p1, 'Зеркальные двери шкафа', 'Айна', 'checkbox', NULL, NULL, 'extras', 48000, 9;

INSERT INTO product_options (product_id, option_name_ru, option_name_kk, option_type, option_value_ru, option_value_kk, option_group, price_modifier, sort_order)
SELECT @p2, 'Материал каркаса', 'Каркас', 'select', 'Сосна', 'Қарағай', 'material', 0, 1
UNION ALL SELECT @p2, 'Материал каркаса', 'Каркас', 'select', 'Берёза', 'Қайың', 'material', 45000, 2
UNION ALL SELECT @p2, 'Материал каркаса', 'Каркас', 'select', 'Массив дуба', 'Емен', 'material', 105000, 3
UNION ALL SELECT @p2, 'Тип основания', 'Негіз', 'select', 'Платформа', 'Платформа', 'config', 0, 4
UNION ALL SELECT @p2, 'Тип основания', 'Негіз', 'select', 'Подъёмный механизм', 'Көтергіш', 'config', 72000, 5
UNION ALL SELECT @p2, 'Тип основания', 'Негіз', 'select', 'С 4 ящиками', '4 сандық', 'config', 58000, 6
UNION ALL SELECT @p2, 'USB в изголовье', 'USB', 'checkbox', NULL, NULL, 'extras', 19000, 7
UNION ALL SELECT @p2, 'Усиленные ламели', 'Ламель', 'checkbox', NULL, NULL, 'extras', 26000, 8;

INSERT INTO product_options (product_id, option_name_ru, option_name_kk, option_type, option_value_ru, option_value_kk, option_group, price_modifier, sort_order)
SELECT @p3, 'Обивка дивана', 'Жабын', 'select', 'Рогожка', 'Сыбыз', 'material', 0, 1
UNION ALL SELECT @p3, 'Обивка дивана', 'Жабын', 'select', 'Микрофибра', 'Микрофибра', 'material', 68000, 2
UNION ALL SELECT @p3, 'Обивка дивана', 'Жабын', 'select', 'Натуральная кожа', 'Тері', 'material', 185000, 3
UNION ALL SELECT @p3, 'Планировка дивана', 'Жоспар', 'select', 'Прямой 3-местный', '3 орын', 'config', 0, 4
UNION ALL SELECT @p3, 'Планировка дивана', 'Жоспар', 'select', 'Угловой L', 'L', 'config', 145000, 5
UNION ALL SELECT @p3, 'Планировка дивана', 'Жоспар', 'select', 'Модульный U', 'U', 'config', 245000, 6
UNION ALL SELECT @p3, 'ТВ-тумба 180 см', 'ТВ-тумба', 'checkbox', NULL, NULL, 'extras', 52000, 7
UNION ALL SELECT @p3, 'Пуф 90×60', 'Пуф', 'checkbox', NULL, NULL, 'extras', 48000, 8
UNION ALL SELECT @p3, 'Настенные полки', 'Сөрелер', 'checkbox', NULL, NULL, 'extras', 28000, 9;

INSERT INTO product_options (product_id, option_name_ru, option_name_kk, option_type, option_value_ru, option_value_kk, option_group, price_modifier, sort_order)
SELECT @p4, 'Обивка кресла', 'Жабын', 'select', 'Рогожка', 'Сыбыз', 'material', 0, 1
UNION ALL SELECT @p4, 'Обивка кресла', 'Жабын', 'select', 'Велюр', 'Велюр', 'material', 32000, 2
UNION ALL SELECT @p4, 'Обивка кресла', 'Жабын', 'select', 'Кожзам', 'ЗИП', 'material', 62000, 3
UNION ALL SELECT @p4, 'Механизм', 'Механизм', 'select', 'Стационарное', 'Стационар', 'config', 0, 4
UNION ALL SELECT @p4, 'Механизм', 'Механизм', 'select', 'Качалка', 'Әнші', 'config', 38000, 5
UNION ALL SELECT @p4, 'Механизм', 'Механизм', 'select', 'Кресло-кровать', 'Кровать', 'config', 72000, 6
UNION ALL SELECT @p4, 'Подголовник', 'Басқыш', 'checkbox', NULL, NULL, 'extras', 16000, 7
UNION ALL SELECT @p4, 'Пуф-куб', 'Пуф', 'checkbox', NULL, NULL, 'extras', 45000, 8;

INSERT INTO product_options (product_id, option_name_ru, option_name_kk, option_type, option_value_ru, option_value_kk, option_group, price_modifier, sort_order)
SELECT @p5, 'Столешница', 'Үстел', 'select', 'ЛДСП дуб', 'ЛДСП', 'material', 0, 1
UNION ALL SELECT @p5, 'Столешница', 'Үстел', 'select', 'Массив дуба', 'Емен', 'material', 88000, 2
UNION ALL SELECT @p5, 'Столешница', 'Үстел', 'select', 'Кварц', 'Кварц', 'material', 165000, 3
UNION ALL SELECT @p5, 'Размер стола', 'Өлшем', 'select', '120×80 (4 места)', '120×80', 'config', 0, 4
UNION ALL SELECT @p5, 'Размер стола', 'Өлшем', 'select', '140×90 (6 мест)', '140×90', 'config', 52000, 5
UNION ALL SELECT @p5, 'Размер стола', 'Өлшем', 'select', '160×90 (8 мест)', '160×90', 'config', 92000, 6
UNION ALL SELECT @p5, 'Стулья +2', 'Орындық', 'checkbox', NULL, NULL, 'extras', 58000, 7
UNION ALL SELECT @p5, 'Светильник', 'Шам', 'checkbox', NULL, NULL, 'extras', 38000, 8
UNION ALL SELECT @p5, 'Ящики в стол', 'Сызба', 'checkbox', NULL, NULL, 'extras', 42000, 9;

INSERT INTO product_options (product_id, option_name_ru, option_name_kk, option_type, option_value_ru, option_value_kk, option_group, price_modifier, sort_order)
SELECT @p6, 'Столешница', 'Үстел', 'select', 'ЛДСП 25 мм', 'ЛДСП', 'material', 0, 1
UNION ALL SELECT @p6, 'Столешница', 'Үстел', 'select', 'Массив дуба', 'Емен', 'material', 72000, 2
UNION ALL SELECT @p6, 'Столешница', 'Үстел', 'select', 'Стекло 8 мм', 'Шыны', 'material', 98000, 3
UNION ALL SELECT @p6, 'Ширина стола', 'Ен', 'select', '120 см', '120', 'config', 0, 4
UNION ALL SELECT @p6, 'Ширина стола', 'Ен', 'select', '140 см', '140', 'config', 32000, 5
UNION ALL SELECT @p6, 'Ширина стола', 'Ен', 'select', '160 см', '160', 'config', 58000, 6
UNION ALL SELECT @p6, 'Кабель-канал', 'Кабель', 'checkbox', NULL, NULL, 'extras', 14000, 7
UNION ALL SELECT @p6, 'Надстройка с полками', 'Сөрелер', 'checkbox', NULL, NULL, 'extras', 52000, 8
UNION ALL SELECT @p6, 'Кресло офисное', 'Кресло', 'checkbox', NULL, NULL, 'extras', 89000, 9;

INSERT INTO product_options (product_id, option_name_ru, option_name_kk, option_type, option_value_ru, option_value_kk, option_group, price_modifier, sort_order)
SELECT @p7, 'Цвет фасада', 'Түс', 'select', 'Белый матовый', 'Ақ', 'material', 0, 1
UNION ALL SELECT @p7, 'Цвет фасада', 'Түс', 'select', 'Дуб', 'Емен', 'material', 28000, 2
UNION ALL SELECT @p7, 'Цвет фасада', 'Түс', 'select', 'Венге', 'Венге', 'material', 34000, 3
UNION ALL SELECT @p7, 'Комплектация', 'Жинақ', 'select', 'Обувница + вешалка', 'Негізгі', 'config', 0, 4
UNION ALL SELECT @p7, 'Комплектация', 'Жинақ', 'select', '+ зеркало 180 см', '+ айна', 'config', 52000, 5
UNION ALL SELECT @p7, 'Комплектация', 'Жинақ', 'select', '+ банкетка', '+ орындық', 'config', 68000, 6
UNION ALL SELECT @p7, 'Доводчики', 'Доводчик', 'checkbox', NULL, NULL, 'extras', 15000, 7
UNION ALL SELECT @p7, 'LED у зеркала', 'LED', 'checkbox', NULL, NULL, 'extras', 28000, 8;

INSERT INTO product_options (product_id, option_name_ru, option_name_kk, option_type, option_value_ru, option_value_kk, option_group, price_modifier, sort_order)
SELECT @p8, 'Фасад', 'Фасад', 'select', 'ЛДСП', 'ЛДСП', 'material', 0, 1
UNION ALL SELECT @p8, 'Фасад', 'Фасад', 'select', 'МДФ белый', 'Ақ МДФ', 'material', 38000, 2
UNION ALL SELECT @p8, 'Фасад', 'Фасад', 'select', 'МДФ дуб', 'Емен', 'material', 55000, 3
UNION ALL SELECT @p8, 'Комплект', 'Жинақ', 'select', 'Кровать + стол', 'Негізгі', 'config', 0, 4
UNION ALL SELECT @p8, 'Комплект', 'Жинақ', 'select', '+ шкаф 2-дверный', '+ шкаф', 'config', 98000, 5
UNION ALL SELECT @p8, 'Комплект', 'Жинақ', 'select', 'Полный комплект', 'Толық', 'config', 185000, 6
UNION ALL SELECT @p8, 'Защита углов', 'Бұрыш', 'checkbox', NULL, NULL, 'extras', 12000, 7
UNION ALL SELECT @p8, 'Магнитная доска', 'Тақта', 'checkbox', NULL, NULL, 'extras', 22000, 8
UNION ALL SELECT @p8, 'Ящики под кроватью', 'Сызба', 'checkbox', NULL, NULL, 'extras', 35000, 9;

SELECT '✅ Цены обновлены' AS status;
