-- =============================================================
-- Восстановление кириллицы в products/categories
-- Запускайте после импорта с неверной кодировкой (в UI было «???????»).
-- Идемпотентно: только UPDATE / upsert по slug и SKU.
-- =============================================================

USE furniture_platform;

UPDATE products SET
    name_ru = 'Quattro Bedroom D1 - спальный гарнитур',
    name_kk = 'Quattro Bedroom D1 - ұйқы жиынтығы',
    description_ru = 'Двуспальная кровать, прикроватные тумбы и шкаф. Настройте обивку, размер спального места и дополнительные модули.'
WHERE sku IN ('BEDROOM-SET-01', 'ETEO-D1-BASE');

UPDATE products SET
    name_ru = 'Quattro Bedroom D2 - кровать с подъёмным механизмом',
    name_kk = 'Quattro Bedroom D2 - көтергіш механизмі',
    description_ru = 'Отдельная кровать с выбором каркаса, типа основания и модулей хранения.'
WHERE sku = 'BEDROOM-BED-02';

UPDATE products SET
    name_ru = 'Quattro Living M3 - гостиная модульная',
    name_kk = 'Quattro Living M3 - модульдік гостиная',
    description_ru = 'Угловой диван, кресла и ТВ-зона. Выберите обивку, форму дивана и дополнительные модули.'
WHERE sku IN ('LIVING-ROOM-SET-01', 'ETEO-M3-BASE');

UPDATE products SET
    name_ru = 'Кресло',
    name_kk = 'Кресло',
    description_ru = 'Компактное кресло для гостиной: обивка, механизм раскладывания и аксессуары.'
WHERE sku = 'LIVING-CHAIR-02';

UPDATE products SET
    name_ru = 'Quattro Kitchen K1 - обеденная группа',
    name_kk = 'Quattro Kitchen K1 - ас үй тобы',
    description_ru = 'Обеденный стол и стулья для кухни-столовой. Настройте столешницу, размер стола и комплектацию.'
WHERE sku = 'KITCHEN-DINING-01';

UPDATE products SET
    name_ru = 'Quattro Office O1 - рабочий кабинет',
    name_kk = 'Quattro Office O1',
    description_ru = 'Письменный стол, стеллаж и кресло для домашнего офиса.'
WHERE sku = 'HOME-OFFICE-01';

UPDATE products SET
    name_ru = 'Quattro Entry E1 - прихожая',
    name_kk = 'Quattro Entry E1',
    description_ru = 'Обувница, вешалка и зеркало в едином стиле.'
WHERE sku = 'ENTRYWAY-01';

UPDATE products SET
    name_ru = 'Quattro Kids K1 - детская',
    name_kk = 'Quattro Kids K1',
    description_ru = 'Кровать, стол и система хранения для детской комнаты.'
WHERE sku = 'KIDS-ROOM-01';

INSERT INTO categories (name_ru, name_kk, slug)
VALUES
('Спальня', 'Ұйқы бөлмесі', 'bedroom'),
('Гостиная', 'Тіл бөлмесі', 'living-room'),
('Кухня', 'Ас үй', 'kitchen'),
('Домашний офис', 'Үйлік офис', 'home-office'),
('Прихожая', 'Кіреу', 'entryway'),
('Детская', 'Балалар бөлмесі', 'kids')
ON DUPLICATE KEY UPDATE
    name_ru = VALUES(name_ru),
    name_kk = VALUES(name_kk);
