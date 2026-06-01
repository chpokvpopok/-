-- =============================================================
-- Скрипт обновления: Офисная мебель → Домашняя мебель
-- Идемпотентный: безопасен при повторном запуске migrate.sh
-- =============================================================

USE furniture_platform;

-- Серии домашней мебели (upsert по slug, без DELETE)
INSERT INTO product_series (slug, name_ru, name_kk, description_ru, description_kk, sort_order)
VALUES
(
    'bedroom-collection',
    'Спальня • Bedroom Collection',
    'Ұйқы бөлмесі • Bedroom Collection',
    'Стильная и комфортная мебель для спальни. Кровати, тумбочки, комоды и шкафы, '
    'созданные для идеального отдыха в вашем доме.',
    'Жатын бөлмесіне арналған стильдi және ыңғайлы жиһаз.',
    1
),
(
    'living-room-collection',
    'Гостиная • Living Room Collection',
    'Тіл бөлмесі • Living Room Collection',
    'Современные диваны, кресла и журнальные столики для уютной гостиной. '
    'Практичный и элегантный дизайн для семейных вечеров.',
    'Гостиная үшін дивандар, креслалар және журнал столдары.',
    2
),
(
    'kitchen-collection',
    'Кухня • Kitchen Collection',
    'Ас үй • Kitchen Collection',
    'Функциональная кухонная мебель: столы, стулья и шкафчики. '
    'Качественные материалы и удобные размеры для приятного готовления.',
    'Ас үйінің жиһазы: столдар, орындықтар және шкафтар.',
    3
),
(
    'home-office-collection',
    'Домашний офис • Home Office Collection',
    'Үйлік офис • Home Office Collection',
    'Производительные рабочие столы и удобные офисные кресла для вашего домашнего кабинета. '
    'Организованное пространство для эффективной работы.',
    'Үйлік офис үшін сауда столдары және офис креслалары.',
    4
)
ON DUPLICATE KEY UPDATE
    name_ru        = VALUES(name_ru),
    name_kk        = VALUES(name_kk),
    description_ru = VALUES(description_ru),
    description_kk = VALUES(description_kk),
    sort_order     = VALUES(sort_order);

-- Привязка товаров к сериям (старый и новый SKU)
UPDATE products p
INNER JOIN product_series ps ON ps.slug = 'bedroom-collection'
SET p.series_id = ps.id, p.slug = 'bedroom-set-1'
WHERE p.sku IN ('ETEO-D1-BASE', 'BEDROOM-SET-01');

UPDATE products p
INNER JOIN product_series ps ON ps.slug = 'living-room-collection'
SET p.series_id = ps.id, p.slug = 'living-room-set-1'
WHERE p.sku IN ('ETEO-M3-BASE', 'LIVING-ROOM-SET-01');

-- Названия и описания товаров
UPDATE products SET
    name_ru = 'Спальный гарнитур Premium Plus',
    name_kk = 'Ұйқы гарнитуры Premium Plus',
    description_ru = 'Премиальный набор для спальни: двуспальная кровать с ортопедическим матрасом, две прикроватные тумбочки и зеркальный шкаф. Массив натурального дерева, мягкие декоративные подушки.',
    description_kk = 'Спальня үшін премиум жиынтығы: ортопедиялық матрастық екі орындық төсек, екі прикроватті шкафша және айналық шкаф.',
    sku = 'BEDROOM-SET-01'
WHERE sku IN ('ETEO-D1-BASE', 'BEDROOM-SET-01');

UPDATE products SET
    name_ru = 'Гостиный гарнитур Comfort Plus',
    name_kk = 'Тіл гарнитуры Comfort Plus',
    description_ru = 'Модульный набор для гостиной: уютный угловой диван, два кресла, журнальный столик и ТВ-тумба. Обивка из качественного текстиля, наполнитель из высокоэластичного поролона.',
    description_kk = 'Гостиная үшін модульдік жиынтық: бұрыштық диван, екі крестал, журнал столы және ТВ-тумба.',
    sku = 'LIVING-ROOM-SET-01'
WHERE sku IN ('ETEO-M3-BASE', 'LIVING-ROOM-SET-01');

-- Компетенции
UPDATE pages SET
    title_ru = 'Понимание вашего образа жизни',
    title_kk = 'Өмір салтыңызды түсіну',
    excerpt_ru = 'Консультируем по выбору мебели, которая идеально подойдёт вашему дому и образу жизни.',
    excerpt_kk = 'Үйіңіз және өмір салтыңызға ұқсас жиһаз таңдауға консультация берейік.'
WHERE slug = 'competence-concept';

UPDATE pages SET
    title_ru = 'Комфорт и функциональность',
    title_kk = 'Ыңғайлылық және функционалдық',
    excerpt_ru = 'Проектируем мебель с учётом эргономики, практичности и долговечности для вашего пространства.',
    excerpt_kk = 'Жиһазды эргономика, практикалық және ұзақ қызмет ету ұзақтығын ескере отырып жобалаймыз.'
WHERE slug = 'competence-ergonomics';

UPDATE pages SET
    title_ru = 'Дизайнерские решения',
    title_kk = 'Дизайнерлік шешімдер',
    excerpt_ru = 'Создаём стильный и гармоничный интерьер, который отражает вашу индивидуальность.',
    excerpt_kk = 'Өздерінің құрылымын көрсетіп тұрған әдемі және үйлісімді интерьер жасаймыз.'
WHERE slug = 'competence-design';

UPDATE pages SET
    title_ru = '3D-визуализация интерьера',
    title_kk = '3D-интерьер визуализациясы',
    excerpt_ru = 'Фотореалистичные 3D-рендеры помогают представить готовый интерьер до покупки мебели.',
    excerpt_kk = 'Фотореалистік 3D-рендерлер жиһаз сатып алмастан дайын интерьерді ойлау үшін көмектеседі.'
WHERE slug = 'competence-visualization';

-- Преимущества
UPDATE pages SET
    title_ru = 'Собственное производство',
    title_kk = 'Өз өндірісі',
    excerpt_ru = 'Полный контроль качества на каждом этапе производства домашней мебели.',
    excerpt_kk = 'Өндірістің әр кезеңінде сапаның толық бақылауы.'
WHERE slug = 'advantage-production';

UPDATE pages SET
    title_ru = 'Натуральные материалы',
    title_kk = 'Табиғи материалдар',
    excerpt_ru = 'Используем только экологичные материалы: натуральное дерево, качественный текстиль и безопасные краски.',
    excerpt_kk = 'Табиғи ағаш, сапалы текстиль және қауіпсіз бояу ғана пайдаланамыз.'
WHERE slug = 'advantage-materials';

UPDATE pages SET
    title_ru = 'Гибкая кастомизация',
    title_kk = 'Икемді кастомизация',
    excerpt_ru = 'Подбираем размеры, цвета и материалы мебели точно под ваше помещение и вкус.',
    excerpt_kk = 'Өлшемдерін, түстерін және материалдарын сіздің бөлмеңіз бен талғамыңызға сәйкес таңдаймыз.'
WHERE slug = 'advantage-custom';

-- Категории (upsert по slug, без DELETE пока есть товары)
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

-- Перепривязка товаров к новым категориям
UPDATE products p
INNER JOIN categories c ON c.slug = 'bedroom'
SET p.category_id = c.id
WHERE p.sku IN ('ETEO-D1-BASE', 'BEDROOM-SET-01');

UPDATE products p
INNER JOIN categories c ON c.slug = 'living-room'
SET p.category_id = c.id
WHERE p.sku IN ('ETEO-M3-BASE', 'LIVING-ROOM-SET-01');

-- Удаление старых офисных категорий (только если на них нет товаров)
DELETE c FROM categories c
LEFT JOIN products p ON p.category_id = c.id
WHERE c.slug IN ('dispetcherskaya', 'tekhnologicheskaya', 'modulnye-sistemy')
  AND p.id IS NULL;

-- Удаление старых офисных серий (только если на них нет товаров)
DELETE ps FROM product_series ps
LEFT JOIN products p ON p.series_id = ps.id
WHERE ps.slug IN ('eteo-one', 'eteo-flow', 'eteo-pulse')
  AND p.id IS NULL;

-- Сайт-настройки
UPDATE site_settings SET
    value_ru = 'Четтро — премиальная домашняя мебель, которая превращает ваш дом в идеальное пространство для жизни',
    value_kk = 'Четтро — сіздің үйіңіз өндіктің идеалды кеңістіке айналдыру үшін премиум үйлік жиһаз'
WHERE setting_key = 'hero_title';

UPDATE site_settings SET
    value_ru = 'Каждое изделие создано с любовью к деталям и заботой о вашем комфорте. Натуральные материалы, современный дизайн, безупречное исполнение.',
    value_kk = 'Әр өнім деталь сүйіспе және сіздің ыңғайлықтағы бағандыға арнайды.'
WHERE setting_key = 'hero_description';

SELECT '✅ Обновление завершено: офисная мебель → домашняя мебель' AS status;
