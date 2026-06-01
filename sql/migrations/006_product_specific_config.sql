-- =============================================================
-- Критерии под каждый продукт, 8 конфигурируемых моделей, фото
-- =============================================================

USE furniture_platform;

-- Slug и превью (пути к тематическим изображениям)
UPDATE products SET slug = 'bedroom-set-1', image_preview = '/public/images/products/bedroom-set.jpg' WHERE sku IN ('BEDROOM-SET-01', 'ETEO-D1-BASE');
UPDATE products SET slug = 'living-room-set-1', image_preview = '/public/images/products/living-room-set.jpg' WHERE sku IN ('LIVING-ROOM-SET-01', 'ETEO-M3-BASE');
UPDATE products SET slug = 'kitchen-dining-01', image_preview = '/public/images/products/kitchen-dining.jpg' WHERE sku = 'KITCHEN-DINING-01';
UPDATE products SET slug = 'home-office-01', image_preview = '/public/images/products/home-office.jpg' WHERE sku = 'HOME-OFFICE-01';
UPDATE products SET slug = 'entryway-01', image_preview = '/public/images/products/entryway.jpg' WHERE sku = 'ENTRYWAY-01';
UPDATE products SET slug = 'kids-room-01', image_preview = '/public/images/products/kids-room.jpg' WHERE sku = 'KIDS-ROOM-01';

INSERT INTO products
    (category_id, name_ru, name_kk, description_ru, description_kk, base_price, sku, active, image_preview, slug)
SELECT c.id,
    'Quattro Bedroom D2 - кровать с подъёмным механизмом',
    'Quattro Bedroom D2 - көтергіш механизмі',
    'Отдельная кровать с выбором каркаса, типа основания и модулей хранения.',
    'Жеке төсек: каркас, негіз және сақтау модульдері.',
    320000.00, 'BEDROOM-BED-02', 1, '/public/images/products/bedroom-bed-02.jpg', 'bedroom-bed-02'
FROM categories c WHERE c.slug = 'bedroom' LIMIT 1
ON DUPLICATE KEY UPDATE name_ru = VALUES(name_ru), description_ru = VALUES(description_ru),
    image_preview = VALUES(image_preview), slug = VALUES(slug), active = 1;

INSERT INTO products
    (category_id, name_ru, name_kk, description_ru, description_kk, base_price, sku, active, image_preview, slug)
SELECT c.id,
    'Quattro Living L2 - кресло-кроват',
    'Quattro Living L2 - кресло-кровать',
    'Компактное кресло для гостиной: обивка, механизм раскладывания и аксессуары.',
    'Гостинаяға арналған кресло: жабын, механизм және аксессуарлар.',
    245000.00, 'LIVING-CHAIR-02', 1, '/public/images/products/living-chair-02.jpg', 'living-chair-02'
FROM categories c WHERE c.slug = 'living-room' LIMIT 1
ON DUPLICATE KEY UPDATE name_ru = VALUES(name_ru), description_ru = VALUES(description_ru),
    image_preview = VALUES(image_preview), slug = VALUES(slug), active = 1;

SET @p1 = (SELECT id FROM products WHERE sku IN ('BEDROOM-SET-01', 'ETEO-D1-BASE') ORDER BY id LIMIT 1);
SET @p2 = (SELECT id FROM products WHERE sku = 'BEDROOM-BED-02' LIMIT 1);
SET @p3 = (SELECT id FROM products WHERE sku IN ('LIVING-ROOM-SET-01', 'ETEO-M3-BASE') ORDER BY id LIMIT 1);
SET @p4 = (SELECT id FROM products WHERE sku = 'LIVING-CHAIR-02' LIMIT 1);
SET @p5 = (SELECT id FROM products WHERE sku = 'KITCHEN-DINING-01' LIMIT 1);
SET @p6 = (SELECT id FROM products WHERE sku = 'HOME-OFFICE-01' LIMIT 1);
SET @p7 = (SELECT id FROM products WHERE sku = 'ENTRYWAY-01' LIMIT 1);
SET @p8 = (SELECT id FROM products WHERE sku = 'KIDS-ROOM-01' LIMIT 1);

DELETE FROM product_options
WHERE product_id IN (@p1, @p2, @p3, @p4, @p5, @p6, @p7, @p8);

-- D1 Спальный гарнитур
INSERT INTO product_options (product_id, option_name_ru, option_name_kk, option_type, option_value_ru, option_value_kk, option_group, price_modifier, sort_order)
SELECT @p1, 'Ткань изголовья', 'Бас жабыны', 'select', 'Лён светлый', 'Ашық зығыр', 'material', 0, 1
UNION ALL SELECT @p1, 'Ткань изголовья', 'Бас жабыны', 'select', 'Велюр графит', 'Графит велюр', 'material', 48000, 2
UNION ALL SELECT @p1, 'Ткань изголовья', 'Бас жабыны', 'select', 'Экокожа капучино', 'Экожа', 'material', 92000, 3
UNION ALL SELECT @p1, 'Размер кровати', 'Төсек өлшемі', 'select', '160×200', '160×200', 'config', 0, 4
UNION ALL SELECT @p1, 'Размер кровати', 'Төсек өлшемі', 'select', '180×200', '180×200', 'config', 38000, 5
UNION ALL SELECT @p1, 'Размер кровати', 'Төсек өлшемі', 'select', '200×200', '200×200', 'config', 72000, 6
UNION ALL SELECT @p1, 'Ортопедический матрас Pro', 'Ортопедиялық матрас', 'checkbox', NULL, NULL, 'extras', 89000, 7
UNION ALL SELECT @p1, 'Зеркальные двери шкафа', 'Айналы есіктер', 'checkbox', NULL, NULL, 'extras', 45000, 8
UNION ALL SELECT @p1, 'Прикроватные тумбы (пара)', 'Тумбалар жұбы', 'checkbox', NULL, NULL, 'extras', 36000, 9;

-- D2 Кровать
INSERT INTO product_options (product_id, option_name_ru, option_name_kk, option_type, option_value_ru, option_value_kk, option_group, price_modifier, sort_order)
SELECT @p2, 'Материал каркаса', 'Каркас материалы', 'select', 'Сосна', 'Қарағай', 'material', 0, 1
UNION ALL SELECT @p2, 'Материал каркаса', 'Каркас материалы', 'select', 'Берёза (фанера)', 'Қайың', 'material', 42000, 2
UNION ALL SELECT @p2, 'Материал каркаса', 'Каркас материалы', 'select', 'Массив дуба', 'Емен', 'material', 98000, 3
UNION ALL SELECT @p2, 'Тип основания', 'Негіз түрі', 'select', 'Платформа', 'Платформа', 'config', 0, 4
UNION ALL SELECT @p2, 'Тип основания', 'Негіз түрі', 'select', 'Подъёмный механизм', 'Көтергіш', 'config', 65000, 5
UNION ALL SELECT @p2, 'Тип основания', 'Негіз түрі', 'select', 'С 4 ящиками', '4 сандық', 'config', 54000, 6
UNION ALL SELECT @p2, 'USB в изголовье', 'USB', 'checkbox', NULL, NULL, 'extras', 18000, 7
UNION ALL SELECT @p2, 'Усиленные ламели', 'Ламельдер', 'checkbox', NULL, NULL, 'extras', 24000, 8;

-- M3 Гостиная
INSERT INTO product_options (product_id, option_name_ru, option_name_kk, option_type, option_value_ru, option_value_kk, option_group, price_modifier, sort_order)
SELECT @p3, 'Обивка дивана', 'Диван жабыны', 'select', 'Рогожка песочная', 'Құмды сыбыз', 'material', 0, 1
UNION ALL SELECT @p3, 'Обивка дивана', 'Диван жабыны', 'select', 'Микрофибра', 'Микрофибра', 'material', 52000, 2
UNION ALL SELECT @p3, 'Обивка дивана', 'Диван жабыны', 'select', 'Натуральная кожа', 'Табиғи тері', 'material', 145000, 3
UNION ALL SELECT @p3, 'Планировка дивана', 'Диван жоспары', 'select', 'Прямой 3-местный', '3 орын', 'config', 0, 4
UNION ALL SELECT @p3, 'Планировка дивана', 'Диван жоспары', 'select', 'Угловой L', 'L бұрыш', 'config', 115000, 5
UNION ALL SELECT @p3, 'Планировка дивана', 'Диван жоспары', 'select', 'Модульный U', 'U модуль', 'config', 190000, 6
UNION ALL SELECT @p3, 'ТВ-тумба 180 см', 'ТВ-тумба', 'checkbox', NULL, NULL, 'extras', 42000, 7
UNION ALL SELECT @p3, 'Пуф 90×60', 'Пуф', 'checkbox', NULL, NULL, 'extras', 38000, 8
UNION ALL SELECT @p3, 'Настенные полки', 'Сөрелер', 'checkbox', NULL, NULL, 'extras', 22000, 9;

-- L2 Кресло
INSERT INTO product_options (product_id, option_name_ru, option_name_kk, option_type, option_value_ru, option_value_kk, option_group, price_modifier, sort_order)
SELECT @p4, 'Обивка кресла', 'Кресло жабыны', 'select', 'Рогожка', 'Сыбыз', 'material', 0, 1
UNION ALL SELECT @p4, 'Обивка кресла', 'Кресло жабыны', 'select', 'Велюр', 'Велюр', 'material', 28000, 2
UNION ALL SELECT @p4, 'Обивка кресла', 'Кресло жабыны', 'select', 'Кожзам', 'ЗИП тері', 'material', 55000, 3
UNION ALL SELECT @p4, 'Механизм', 'Механизм', 'select', 'Стационарное', 'Стационар', 'config', 0, 4
UNION ALL SELECT @p4, 'Механизм', 'Механизм', 'select', 'Качалка', 'Әнші', 'config', 32000, 5
UNION ALL SELECT @p4, 'Механизм', 'Механизм', 'select', 'Кресло-кровать', 'Кровать', 'config', 68000, 6
UNION ALL SELECT @p4, 'Подголовник регулируемый', 'Басқыш', 'checkbox', NULL, NULL, 'extras', 15000, 7
UNION ALL SELECT @p4, 'Пуф-куб в комплекте', 'Пуф', 'checkbox', NULL, NULL, 'extras', 42000, 8;

-- K1 Кухня
INSERT INTO product_options (product_id, option_name_ru, option_name_kk, option_type, option_value_ru, option_value_kk, option_group, price_modifier, sort_order)
SELECT @p5, 'Столешница', 'Үстел беті', 'select', 'ЛДСП дуб', 'ЛДСП', 'material', 0, 1
UNION ALL SELECT @p5, 'Столешница', 'Үстел беті', 'select', 'Массив дуба', 'Емен', 'material', 82000, 2
UNION ALL SELECT @p5, 'Столешница', 'Үстел беті', 'select', 'Кварц Silestone', 'Кварц', 'material', 155000, 3
UNION ALL SELECT @p5, 'Размер обеденного стола', 'Үстел өлшемі', 'select', '120×80 (4 места)', '120×80', 'config', 0, 4
UNION ALL SELECT @p5, 'Размер обеденного стола', 'Үстел өлшемі', 'select', '140×90 (6 мест)', '140×90', 'config', 45000, 5
UNION ALL SELECT @p5, 'Размер обеденного стола', 'Үстел өлшемі', 'select', '160×90 (8 мест)', '160×90', 'config', 82000, 6
UNION ALL SELECT @p5, 'Стулья +2', 'Орындық +2', 'checkbox', NULL, NULL, 'extras', 52000, 7
UNION ALL SELECT @p5, 'Подвесной свет над столом', 'Шам', 'checkbox', NULL, NULL, 'extras', 35000, 8
UNION ALL SELECT @p5, 'Выдвижные ящики', 'Сызбалар', 'checkbox', NULL, NULL, 'extras', 38000, 9;

-- O1 Офис
INSERT INTO product_options (product_id, option_name_ru, option_name_kk, option_type, option_value_ru, option_value_kk, option_group, price_modifier, sort_order)
SELECT @p6, 'Столешница стола', 'Үстел', 'select', 'ЛДСП 25 мм', 'ЛДСП', 'material', 0, 1
UNION ALL SELECT @p6, 'Столешница стола', 'Үстел', 'select', 'Массив дуба', 'Емен', 'material', 68000, 2
UNION ALL SELECT @p6, 'Столешница стола', 'Үстел', 'select', 'Закалённое стекло', 'Шыны', 'material', 95000, 3
UNION ALL SELECT @p6, 'Ширина рабочего стола', 'Ен', 'select', '120 см', '120', 'config', 0, 4
UNION ALL SELECT @p6, 'Ширина рабочего стола', 'Ен', 'select', '140 см', '140', 'config', 28000, 5
UNION ALL SELECT @p6, 'Ширина рабочего стола', 'Ен', 'select', '160 см', '160', 'config', 52000, 6
UNION ALL SELECT @p6, 'Кабель-канал в столешнице', 'Кабель', 'checkbox', NULL, NULL, 'extras', 12000, 7
UNION ALL SELECT @p6, 'Надстройка с полками', 'Сөрелер', 'checkbox', NULL, NULL, 'extras', 45000, 8
UNION ALL SELECT @p6, 'Эргономичное кресло', 'Кресло', 'checkbox', NULL, NULL, 'extras', 78000, 9;

-- E1 Прихожая
INSERT INTO product_options (product_id, option_name_ru, option_name_kk, option_type, option_value_ru, option_value_kk, option_group, price_modifier, sort_order)
SELECT @p7, 'Цвет фасада', 'Фасад түсі', 'select', 'Белый матовый', 'Ақ', 'material', 0, 1
UNION ALL SELECT @p7, 'Цвет фасада', 'Фасад түсі', 'select', 'Дуб натуральный', 'Емен', 'material', 32000, 2
UNION ALL SELECT @p7, 'Цвет фасада', 'Фасад түсі', 'select', 'Венге', 'Венге', 'material', 38000, 3
UNION ALL SELECT @p7, 'Комплектация', 'Жинақ', 'select', 'Обувница + вешалка', 'Аяқ + аспап', 'config', 0, 4
UNION ALL SELECT @p7, 'Комплектация', 'Жинақ', 'select', '+ зеркало 180 см', '+ айна', 'config', 48000, 5
UNION ALL SELECT @p7, 'Комплектация', 'Жинақ', 'select', '+ банкетка', '+ орындық', 'config', 62000, 6
UNION ALL SELECT @p7, 'Доводчики на ящики', 'Доводчик', 'checkbox', NULL, NULL, 'extras', 14000, 7
UNION ALL SELECT @p7, 'LED-подсветка зеркала', 'LED', 'checkbox', NULL, NULL, 'extras', 26000, 8;

-- K1 Детская
INSERT INTO product_options (product_id, option_name_ru, option_name_kk, option_type, option_value_ru, option_value_kk, option_group, price_modifier, sort_order)
SELECT @p8, 'Фасад и кромка', 'Фасад', 'select', 'ЛДСП сонный синий', 'ЛДСП', 'material', 0, 1
UNION ALL SELECT @p8, 'Фасад и кромка', 'Фасад', 'select', 'МДФ белый', 'Ақ МДФ', 'material', 35000, 2
UNION ALL SELECT @p8, 'Фасад и кромка', 'Фасад', 'select', 'МДФ дуб + пастель', 'Емен МДФ', 'material', 52000, 3
UNION ALL SELECT @p8, 'Комплект мебели', 'Жинақ', 'select', 'Кровать + стол', 'Төсек+стол', 'config', 0, 4
UNION ALL SELECT @p8, 'Комплект мебели', 'Жинақ', 'select', '+ шкаф 2-дверный', '+ шкаф', 'config', 88000, 5
UNION ALL SELECT @p8, 'Комплект мебели', 'Жинақ', 'select', 'Полный (кровать, стол, шкаф, стеллаж)', 'Толық', 'config', 165000, 6
UNION ALL SELECT @p8, 'Защита углов', 'Бұрыш', 'checkbox', NULL, NULL, 'extras', 12000, 7
UNION ALL SELECT @p8, 'Магнитная доска 60×40', 'Тақта', 'checkbox', NULL, NULL, 'extras', 18000, 8
UNION ALL SELECT @p8, 'Выкатные ящики под кроватью', 'Сызба', 'checkbox', NULL, NULL, 'extras', 32000, 9;

SELECT '✅ 8 моделей с индивидуальными критериями конфигуратора' AS status;
