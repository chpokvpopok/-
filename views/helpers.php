<?php

declare(strict_types=1);

/**
 * Экранирует строку для вывода в HTML.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Форматирует цену в тенге.
 */
function format_price(float $value): string
{
    return number_format($value, 0, '.', ' ') . ' ₸';
}

/**
 * Проверяет наличие файла в public/ и возвращает URL или placeholder.
 */
function public_image_url(string $publicPath, string $placeholder = '/public/images/placeholder.webp'): string
{
    $normalized = str_starts_with($publicPath, '/public/')
        ? substr($publicPath, strlen('/public'))
        : $publicPath;

    $filePath = dirname(__DIR__) . '/public' . $normalized;

    return is_file($filePath) ? $publicPath : $placeholder;
}

/**
 * Возвращает URL превью товара или placeholder, если файла нет.
 */
function product_image(?string $path): string
{
    $placeholder = '/public/images/placeholder.webp';

    if ($path === null || trim($path) === '') {
        return $placeholder;
    }

    return public_image_url($path, $placeholder);
}

/**
 * Превью категории каталога по slug.
 */
function category_image(string $slug): string
{
    $paths = [
        'bedroom'     => '/public/images/categories/bedroom.jpg',
        'living-room' => '/public/images/categories/living-room.jpg',
        'kitchen'     => '/public/images/categories/kitchen.jpg',
        'home-office' => '/public/images/categories/home-office.jpg',
        'entryway'    => '/public/images/categories/entryway.jpg',
        'kids'        => '/public/images/categories/kids.jpg',
    ];

    return public_image_url($paths[$slug] ?? '/public/images/placeholder.webp');
}

/**
 * Канонический ключ SKU для метаданных конфигуратора.
 */
function product_sku_key(string $sku): string
{
    return match ($sku) {
        'ETEO-D1-BASE' => 'BEDROOM-SET-01',
        'ETEO-M3-BASE' => 'LIVING-ROOM-SET-01',
        default        => $sku,
    };
}

/**
 * Ссылка на карточку товара (slug предпочтительнее id).
 *
 * @param array{id?: int|string, slug?: string|null} $product
 */
function product_href(array $product): string
{
    $slug = trim((string)($product['slug'] ?? ''));

    if ($slug !== '') {
        return '/product/' . rawurlencode($slug);
    }

    return '/product/' . (int)($product['id'] ?? 0);
}

/**
 * Тексты конфигуратора, привязанные к конкретной модели.
 *
 * @return array{intro: string, groups: array<string, string>}|null
 */
function product_configurator_meta(string $sku): ?array
{
    $meta = [
        'BEDROOM-SET-01' => [
            'intro'  => 'Настройте спальный гарнитур: ткань изголовья, размер кровати и модули хранения.',
            'groups' => [
                'material' => 'Ткань изголовья и боковин',
                'config'   => 'Размер кровати',
                'extras'   => 'Сон и системы хранения',
            ],
        ],
        'BEDROOM-BED-02' => [
            'intro'  => 'Кровать D2: выберите материал каркаса, тип основания и удобства.',
            'groups' => [
                'material' => 'Материал каркаса',
                'config'   => 'Тип основания',
                'extras'   => 'Комфорт и электрика',
            ],
        ],
        'LIVING-ROOM-SET-01' => [
            'intro'  => 'Гостиный гарнитур M3: обивка дивана, планировка и модули под ТВ-зону.',
            'groups' => [
                'material' => 'Обивка дивана',
                'config'   => 'Планировка дивана',
                'extras'   => 'Модули гостиной',
            ],
        ],
        'LIVING-CHAIR-02' => [
            'intro'  => 'Выберите обивку и дополнительные опции.',
            'groups' => [
                'material' => 'Обивка кресла',
                'extras'   => 'Дополнительно',
            ],
        ],
        'KITCHEN-DINING-01' => [
            'intro'  => 'Обеденная группа K1: столешница, размер стола и комплект стульев.',
            'groups' => [
                'material' => 'Столешница',
                'config'   => 'Размер обеденного стола',
                'extras'   => 'Освещение и хранение',
            ],
        ],
        'HOME-OFFICE-01' => [
            'intro'  => 'Домашний офис O1: столешница, ширина стола и эргономичные дополнения.',
            'groups' => [
                'material' => 'Столешница стола',
                'config'   => 'Ширина рабочего стола',
                'extras'   => 'Организация рабочего места',
            ],
        ],
        'ENTRYWAY-01' => [
            'intro'  => 'Прихожая E1: фасад, комплектация (обувница, зеркало, банкетка) и подсветка.',
            'groups' => [
                'material' => 'Цвет фасада',
                'config'   => 'Комплектация',
                'extras'   => 'Фурнитура и свет',
            ],
        ],
        'KIDS-ROOM-01' => [
            'intro'  => 'Детская K1: фасады, комплект (кровать, стол, шкаф) и безопасные опции.',
            'groups' => [
                'material' => 'Фасад и кромка',
                'config'   => 'Комплект мебели',
                'extras'   => 'Безопасность и учёба',
            ],
        ],
    ];

    return $meta[product_sku_key($sku)] ?? null;
}

/**
 * Товары с опциями конфигуратора (для карусели на главной).
 *
 * @param list<array<string, mixed>> $products
 * @return list<array<string, mixed>>
 */
function filter_configurable_products(array $products): array
{
    return array_values(array_filter(
        $products,
        static fn(array $product): bool => !empty($product['options'])
    ));
}

/**
 * Подпись варианта в конфигураторе: для select — значение (Велюр), для checkbox — название опции.
 */
function option_choice_label(array $option): string
{
    $type = (string)($option['option_type'] ?? 'select');

    if ($type === 'select') {
        $value = trim((string)($option['option_value'] ?? ''));

        return $value !== '' ? $value : trim((string)($option['option_name'] ?? ''));
    }

    return trim((string)($option['option_name'] ?? ''));
}

/**
 * Группы реальных вариантов конфигуратора для товара (для карусели и каталога).
 *
 * @return list<array{title: string, values: list<string>}>
 */
function product_configurator_preview_groups(array $product): array
{
    $sku   = (string)($product['sku'] ?? '');
    $meta  = product_configurator_meta($sku);
    $order = ['material', 'config', 'extras'];
    $bucket = [];

    foreach ($product['options'] ?? [] as $option) {
        $group = (string)($option['option_group'] ?? 'other');
        $label = option_choice_label($option);

        if ($label === '') {
            continue;
        }

        if (!isset($bucket[$group])) {
            $bucket[$group] = [];
        }

        if (!in_array($label, $bucket[$group], true)) {
            $bucket[$group][] = $label;
        }
    }

    $groups = [];

    foreach ($order as $groupKey) {
        if (empty($bucket[$groupKey])) {
            continue;
        }

        $title = $meta['groups'][$groupKey] ?? match ($groupKey) {
            'material' => 'Материал',
            'config'   => 'Конфигурация',
            'extras'   => 'Дополнительно',
            default    => ucfirst($groupKey),
        };

        $groups[] = [
            'title'  => $title,
            'values' => $bucket[$groupKey],
        ];
    }

    return $groups;
}

/**
 * @param list<array<string, mixed>> $allProducts
 * @return list<array{label: string, href: string}>
 */
function build_category_configurator_variants(int $categoryId, array $allProducts): array
{
    $variants = [];

    foreach ($allProducts as $product) {
        if ((int)($product['category_id'] ?? 0) !== $categoryId) {
            continue;
        }

        if (empty($product['options'])) {
            continue;
        }

        $variants[] = [
            'label' => trim((string)($product['name'] ?? 'Модель')),
            'href'  => product_href($product),
        ];
    }

    if ($variants !== []) {
        $variants[] = [
            'label' => 'Все модели категории',
            'href'  => '/catalog/' . $categoryId,
        ];
    }

    return $variants;
}

/**
 * Slug категорий для блока «Наши направления» на главной.
 *
 * @return list<string>
 */
function home_catalog_direction_slugs(): array
{
    return ['bedroom', 'living-room', 'kitchen'];
}

/**
 * Метаданные направлений каталога (по slug категории из БД).
 *
 * @return array<string, array{title: string, description: string}>
 */
function catalog_direction_meta_by_slug(): array
{
    return [
        'bedroom' => [
            'title'       => 'Спальня',
            'description' => 'Кровати, тумбы, комоды и системы хранения для комфортного отдыха.',
        ],
        'living-room' => [
            'title'       => 'Гостиная',
            'description' => 'Диваны, кресла, журнальные столики и модульные решения для семейного отдыха.',
        ],
        'kitchen' => [
            'title'       => 'Кухня',
            'description' => 'Столы, стулья и функциональная мебель для удобного приготовления и хранения.',
        ],
        'home-office' => [
            'title'       => 'Домашний офис',
            'description' => 'Рабочие столы, кресла и системы хранения для продуктивной работы дома.',
        ],
        'entryway' => [
            'title'       => 'Прихожая',
            'description' => 'Вешалки, обувницы и компактные системы хранения для порядка у входа.',
        ],
        'kids' => [
            'title'       => 'Детская',
            'description' => 'Безопасная и эргономичная мебель для сна, учёбы и игр.',
        ],
    ];
}

/**
 * @param array{id: int|string, slug?: string, name?: string} $category
 * @param list<array<string, mixed>> $allProducts
 * @return array{
 *     id: int,
 *     title: string,
 *     description: string,
 *     variants: list<array{label: string, href: string}>
 * }|null
 */
function get_catalog_direction_for_category(array $category, array $allProducts = []): ?array
{
    $slug = (string)($category['slug'] ?? '');
    $meta = catalog_direction_meta_by_slug()[$slug] ?? null;

    if ($meta === null) {
        return null;
    }

    $categoryId = (int)$category['id'];

    return [
        'id'          => $categoryId,
        'slug'        => $slug,
        'title'       => $meta['title'],
        'description' => $meta['description'],
        'variants'    => build_category_configurator_variants($categoryId, $allProducts),
    ];
}

/**
 * Направления для главной (по slug, в фиксированном порядке).
 *
 * @param list<array{id: int|string, slug: string, name: string}> $categories
 * @return list<array{
 *     id: int,
 *     title: string,
 *     description: string,
 *     variants: list<array{label: string, href: string}>
 * }>
 */
function get_home_catalog_directions(array $categories, array $allProducts = []): array
{
    $bySlug = [];

    foreach ($categories as $category) {
        $bySlug[(string)($category['slug'] ?? '')] = $category;
    }

    $directions = [];

    foreach (home_catalog_direction_slugs() as $slug) {
        if (!isset($bySlug[$slug])) {
            continue;
        }

        $direction = get_catalog_direction_for_category($bySlug[$slug], $allProducts);

        if ($direction !== null) {
            $directions[] = $direction;
        }
    }

    return $directions;
}

/**
 * @return list<array{label: string, href: string}>
 */
function get_catalog_variants_for_category(array $category, array $allProducts = []): array
{
    return get_catalog_direction_for_category($category, $allProducts)['variants'] ?? [];
}

/**
 * @param array{id: int|string, slug?: string} $category
 * @param array{id: int|string, slug?: string|null} $product
 * @return list<array{label: string, href: string}>
 */
function get_catalog_variants_except_product(array $category, array $product, array $allProducts = []): array
{
    $currentHref = product_href($product);

    return array_values(array_filter(
        get_catalog_variants_for_category($category, $allProducts),
        static fn(array $variant): bool => $variant['href'] !== $currentHref
    ));
}
