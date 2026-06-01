-- Кресло: короткое название без «L2»

USE furniture_platform;

UPDATE products SET
    name_ru = 'Кресло',
    name_kk = 'Кресло'
WHERE sku = 'LIVING-CHAIR-02';
