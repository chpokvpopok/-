<?php
// Быстрая миграция в БД

require 'config/Database.php';

$db = Database::getInstance();

// Очищаем старые данные
$db->exec("DELETE FROM product_series");
$db->exec("DELETE FROM categories");

// Добавляем новые серии домашней мебели
$db->exec("
    INSERT INTO product_series (slug, name_ru, name_kk, description_ru, description_kk, sort_order)
    VALUES
    ('bedroom-collection', 'Спальня', 'Ұйқы бөлмесі', 'Стильная спальня для вашего комфорта', 'Ұйқы үшін стильдi жиһаз', 1),
    ('living-room-collection', 'Гостиная', 'Тіл бөлмесі', 'Уютные диваны и кресла', 'Гостиная үшін дивандар', 2),
    ('kitchen-collection', 'Кухня', 'Ас үй', 'Функциональная кухонная мебель', 'Ас үй жиһазы', 3),
    ('home-office-collection', 'Домашний офис', 'Үйлік офис', 'Рабочие столы для дома', 'Үйлік офис үшін столдар', 4)
");

// Добавляем категории
$db->exec("
    INSERT INTO categories (name_ru, name_kk, slug)
    VALUES
    ('Спальня', 'Ұйқы бөлмесі', 'bedroom'),
    ('Гостиная', 'Тіл бөлмесі', 'living-room'),
    ('Кухня', 'Ас үй', 'kitchen'),
    ('Домашний офис', 'Үйлік офис', 'home-office'),
    ('Прихожая', 'Кіреу', 'entryway'),
    ('Детская', 'Балалар бөлмесі', 'kids')
");

// Обновляем названия товаров
$db->exec("
    UPDATE products SET
        name_ru = 'Спальный гарнитур Premium Plus',
        name_kk = 'Ұйқы гарнитуры Premium Plus',
        description_ru = 'Премиальный набор для спальни: двуспальная кровать, две тумбочки, шкаф',
        description_kk = 'Ұйқы үшін премиум жиынтығы',
        sku = 'BEDROOM-SET-01'
    WHERE sku = 'ETEO-D1-BASE'
");

$db->exec("
    UPDATE products SET
        name_ru = 'Гостиный гарнитур Comfort Plus',
        name_kk = 'Тіл гарнитуры Comfort Plus',
        description_ru = 'Модульный диван, два кресла, журнальный столик',
        description_kk = 'Гостиная үшін модульдік жиынтық',
        sku = 'LIVING-ROOM-SET-01'
    WHERE sku = 'ETEO-M3-BASE'
");

echo "✅ БД обновлена: офисная мебель → домашняя мебель\n";
?>
