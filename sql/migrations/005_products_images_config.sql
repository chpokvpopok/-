-- =============================================================
-- Изображения, модели каталога и критерии конфигуратора
-- Идемпотентно: upsert по SKU, опции пересоздаются по product_id
-- =============================================================

USE furniture_platform;

-- Превью существующих конфигурируемых моделей
UPDATE products SET
    image_preview = '/public/images/products/bedroom-set.jpg',
    name_ru = 'Quattro Bedroom D1 — спальный гарнитур',
    name_kk = 'Quattro Bedroom D1 — ұйқы жиынтығы',
    description_ru = 'Двуспальная кровать, прикроватные тумбы и шкаф. Настройте обивку, размер спального места и дополнительные модули.',
    description_kk = 'Екі орындық төсек, тумбалар және шкаф. Жиһазды өз қалауыңыз бойынша теңшеңіз.',
    base_price = 690000.00
WHERE sku IN ('BEDROOM-SET-01', 'ETEO-D1-BASE');

UPDATE products SET
    image_preview = '/public/images/products/living-room-set.jpg',
    name_ru = 'Quattro Living M3 — гостиная модульная',
    name_kk = 'Quattro Living M3 — модульдік гостиная',
    description_ru = 'Угловой диван, кресла и ТВ-зона. Выберите обивку, форму дивана и дополнительные модули.',
    description_kk = 'Бұрыштық диван, креслалар және ТВ-аймағы. Материал мен конфигурацияны таңдаңыз.',
    base_price = 980000.00
WHERE sku IN ('LIVING-ROOM-SET-01', 'ETEO-M3-BASE');

-- Кухня: третья модель с конфигуратором
INSERT INTO products
    (category_id, name_ru, name_kk, description_ru, description_kk, base_price, sku, active, image_preview, slug)
SELECT
    c.id,
    'Quattro Kitchen K1 — обеденная группа',
    'Quattro Kitchen K1 — ас үй тобы',
    'Обеденный стол и стулья для кухни-столовой. Настройте столешницу, размер стола и комплектацию.',
    'Ас үй-асханаға арналған үстел мен орындықтар.',
    420000.00,
    'KITCHEN-DINING-01',
    1,
    '/public/images/products/kitchen-dining.jpg',
    'kitchen-dining-01'
FROM categories c
WHERE c.slug = 'kitchen'
LIMIT 1
ON DUPLICATE KEY UPDATE
    category_id    = VALUES(category_id),
    name_ru        = VALUES(name_ru),
    name_kk        = VALUES(name_kk),
    description_ru = VALUES(description_ru),
    description_kk = VALUES(description_kk),
    base_price     = VALUES(base_price),
    image_preview  = VALUES(image_preview),
    slug           = VALUES(slug),
    active         = 1;

-- Дополнительные модели каталога (без полного конфигуратора)
INSERT INTO products
    (category_id, name_ru, name_kk, description_ru, description_kk, base_price, sku, active, image_preview, slug)
SELECT c.id, 'Quattro Office O1 — рабочий кабинет', 'Quattro Office O1',
    'Письменный стол, стеллаж и кресло для домашнего офиса.', 'Үйлік офис жиынтығы.',
    385000.00, 'HOME-OFFICE-01', 1, '/public/images/products/home-office.jpg', 'home-office-01'
FROM categories c WHERE c.slug = 'home-office' LIMIT 1
ON DUPLICATE KEY UPDATE name_ru = VALUES(name_ru), image_preview = VALUES(image_preview), active = 1;

INSERT INTO products
    (category_id, name_ru, name_kk, description_ru, description_kk, base_price, sku, active, image_preview, slug)
SELECT c.id, 'Quattro Entry E1 — прихожая', 'Quattro Entry E1',
    'Обувница, вешалка и зеркало в едином стиле.', 'Кіреберіс жиынтығы.',
    195000.00, 'ENTRYWAY-01', 1, '/public/images/products/entryway.jpg', 'entryway-01'
FROM categories c WHERE c.slug = 'entryway' LIMIT 1
ON DUPLICATE KEY UPDATE name_ru = VALUES(name_ru), image_preview = VALUES(image_preview), active = 1;

INSERT INTO products
    (category_id, name_ru, name_kk, description_ru, description_kk, base_price, sku, active, image_preview, slug)
SELECT c.id, 'Quattro Kids K1 — детская', 'Quattro Kids K1',
    'Кровать, стол и система хранения для детской комнаты.', 'Балалар бөлмесі жиынтығы.',
    510000.00, 'KIDS-ROOM-01', 1, '/public/images/products/kids-room.jpg', 'kids-room-01'
FROM categories c WHERE c.slug = 'kids' LIMIT 1
ON DUPLICATE KEY UPDATE name_ru = VALUES(name_ru), image_preview = VALUES(image_preview), active = 1;

-- Критерии конфигуратора: product_id по SKU
SET @p_bedroom = (SELECT id FROM products WHERE sku IN ('BEDROOM-SET-01', 'ETEO-D1-BASE') ORDER BY id LIMIT 1);
SET @p_living  = (SELECT id FROM products WHERE sku IN ('LIVING-ROOM-SET-01', 'ETEO-M3-BASE') ORDER BY id LIMIT 1);
SET @p_kitchen = (SELECT id FROM products WHERE sku = 'KITCHEN-DINING-01' LIMIT 1);

DELETE FROM product_options WHERE product_id IN (@p_bedroom, @p_living, @p_kitchen);

-- Bedroom D1: материал / размер / доп. модули
INSERT INTO product_options
    (product_id, option_name_ru, option_name_kk, option_type, option_value_ru, option_value_kk, option_group, price_modifier, sort_order)
SELECT @p_bedroom, 'Материал обивки', 'Жабын материалы', 'select', 'Ткань Premium', 'Premium мата', 'material', 0.00, 1
UNION ALL SELECT @p_bedroom, 'Материал обивки', 'Жабын материалы', 'select', 'Велюр', 'Велюр', 'material', 55000.00, 2
UNION ALL SELECT @p_bedroom, 'Материал обивки', 'Жабын материалы', 'select', 'Натуральная кожа', 'Табиғи тері', 'material', 120000.00, 3
UNION ALL SELECT @p_bedroom, 'Размер спального места', 'Ұйқы өлшемі', 'select', '160×200 см', '160×200 см', 'config', 0.00, 4
UNION ALL SELECT @p_bedroom, 'Размер спального места', 'Ұйқы өлшемі', 'select', '180×200 см', '180×200 см', 'config', 35000.00, 5
UNION ALL SELECT @p_bedroom, 'Размер спального места', 'Ұйқы өлшемі', 'select', '200×200 см', '200×200 см', 'config', 70000.00, 6
UNION ALL SELECT @p_bedroom, 'Ортопедический матрас', 'Ортопедиялық матрас', 'checkbox', NULL, NULL, 'extras', 85000.00, 7
UNION ALL SELECT @p_bedroom, 'Подсветка изголовья', 'Бас жарықтандыру', 'checkbox', NULL, NULL, 'extras', 28000.00, 8
UNION ALL SELECT @p_bedroom, 'Зеркальные фасады шкафа', 'Айналы фасад', 'checkbox', NULL, NULL, 'extras', 42000.00, 9;

-- Living M3: обивка / форма дивана / модули
INSERT INTO product_options
    (product_id, option_name_ru, option_name_kk, option_type, option_value_ru, option_value_kk, option_group, price_modifier, sort_order)
SELECT @p_living, 'Материал обивки', 'Жабын материалы', 'select', 'Рогожка', 'Сыбыз', 'material', 0.00, 1
UNION ALL SELECT @p_living, 'Материал обивки', 'Жабын материалы', 'select', 'Микрофибра', 'Микрофибра', 'material', 45000.00, 2
UNION ALL SELECT @p_living, 'Материал обивки', 'Жабын материалы', 'select', 'Кожзам', 'ЗИП тері', 'material', 95000.00, 3
UNION ALL SELECT @p_living, 'Конфигурация дивана', 'Диван конфигурациясы', 'select', 'Прямой 3-местный', 'Тура 3 орын', 'config', 0.00, 4
UNION ALL SELECT @p_living, 'Конфигурация дивана', 'Диван конфигурациясы', 'select', 'Угловой L-образный', 'L пішінді', 'config', 110000.00, 5
UNION ALL SELECT @p_living, 'Конфигурация дивана', 'Диван конфигурациясы', 'select', 'Модульный U-образный', 'U модульдік', 'config', 185000.00, 6
UNION ALL SELECT @p_living, 'ТВ-тумба с кабель-каналом', 'ТВ-тумба', 'checkbox', NULL, NULL, 'extras', 38000.00, 7
UNION ALL SELECT @p_living, 'Пуф в комплекте', 'Пуф', 'checkbox', NULL, NULL, 'extras', 52000.00, 8
UNION ALL SELECT @p_living, 'Настенные полки (2 шт.)', 'Сөрелер', 'checkbox', NULL, NULL, 'extras', 24000.00, 9;

-- Kitchen K1: столешница / размер / стулья
INSERT INTO product_options
    (product_id, option_name_ru, option_name_kk, option_type, option_value_ru, option_value_kk, option_group, price_modifier, sort_order)
SELECT @p_kitchen, 'Столешница', 'Үстел беті', 'select', 'ЛДСП дуб', 'ЛДСП емен', 'material', 0.00, 1
UNION ALL SELECT @p_kitchen, 'Столешница', 'Үстел беті', 'select', 'Массив дуба', 'Емен массиві', 'material', 75000.00, 2
UNION ALL SELECT @p_kitchen, 'Столешница', 'Үстел беті', 'select', 'Камень кварц', 'Кварц тас', 'material', 140000.00, 3
UNION ALL SELECT @p_kitchen, 'Размер стола', 'Үстел өлшемі', 'select', '120×80 см (4 места)', '120×80', 'config', 0.00, 4
UNION ALL SELECT @p_kitchen, 'Размер стола', 'Үстел өлшемі', 'select', '140×90 см (6 мест)', '140×90', 'config', 42000.00, 5
UNION ALL SELECT @p_kitchen, 'Размер стола', 'Үстел өлшемі', 'select', '160×90 см (8 мест)', '160×90', 'config', 78000.00, 6
UNION ALL SELECT @p_kitchen, 'Стулья +2 (итого 6)', 'Орындық +2', 'checkbox', NULL, NULL, 'extras', 48000.00, 7
UNION ALL SELECT @p_kitchen, 'Подвесной светильник', 'Шам', 'checkbox', NULL, NULL, 'extras', 32000.00, 8
UNION ALL SELECT @p_kitchen, 'Выдвижные ящики в стол', 'Сызбалар', 'checkbox', NULL, NULL, 'extras', 36000.00, 9;

SELECT '✅ Изображения, модели и критерии конфигуратора обновлены' AS status;
