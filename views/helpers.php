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
 * Возвращает URL превью товара или placeholder, если файла нет.
 */
function product_image(?string $path): string
{
    $placeholder = '/public/images/placeholder.webp';

    if ($path === null || trim($path) === '') {
        return $placeholder;
    }

    $normalized = str_starts_with($path, '/public/')
        ? substr($path, strlen('/public'))
        : $path;

    $filePath = dirname(__DIR__) . '/public' . $normalized;

    return is_file($filePath) ? $path : $placeholder;
}

/**
 * Направления каталога Quattro с вариантами мебели.
 *
 * @return array<int, array{
 *     id: int,
 *     title: string,
 *     description: string,
 *     variants: list<array{label: string, href: string}>
 * }>
 */
function get_catalog_directions(): array
{
    return [
        1 => [
            'id'          => 1,
            'title'       => 'Диспетчерские пульты',
            'description' => 'Оптимизированная мебель для центров управления и системных операторов.',
            'variants'    => [
                ['label' => 'Quattro D1 — диспетчерский пульт', 'href' => '/product/1'],
                ['label' => 'Quattro M3 — модульный комплекс', 'href' => '/product/2'],
                ['label' => 'Операторские консоли', 'href' => '/catalog/1#variants'],
                ['label' => 'Стойки под мониторы', 'href' => '/catalog/1#variants'],
            ],
        ],
        2 => [
            'id'          => 2,
            'title'       => 'Офис / технологическая',
            'description' => 'Удобные рабочие станции и конференц-решения для команд и лабораторий.',
            'variants'    => [
                ['label' => 'Рабочие станции', 'href' => '/catalog/2#variants'],
                ['label' => 'Конференц-столы', 'href' => '/catalog/2#variants'],
                ['label' => 'Лабораторные столы', 'href' => '/catalog/2#variants'],
                ['label' => 'Стеллажи и системы хранения', 'href' => '/catalog/2#variants'],
            ],
        ],
        3 => [
            'id'          => 3,
            'title'       => 'Модульные комплексы',
            'description' => 'Мобильные и масштабируемые конструкции для задач любой сложности.',
            'variants'    => [
                ['label' => 'Мобильные блоки', 'href' => '/catalog/3#variants'],
                ['label' => 'Сборные кабинеты', 'href' => '/catalog/3#variants'],
                ['label' => 'Зоны переговоров', 'href' => '/catalog/3#variants'],
                ['label' => 'Коворкинг-модули', 'href' => '/catalog/3#variants'],
            ],
        ],
    ];
}

/**
 * @return array{
 *     id: int,
 *     title: string,
 *     description: string,
 *     variants: list<array{label: string, href: string}>
 * }|null
 */
function get_catalog_direction(int $categoryId): ?array
{
    $directions = get_catalog_directions();

    return $directions[$categoryId] ?? null;
}

/**
 * @return list<array{label: string, href: string}>
 */
function get_catalog_variants(int $categoryId): array
{
    return get_catalog_direction($categoryId)['variants'] ?? [];
}

/**
 * Варианты направления без текущего товара (для карточки продукта).
 *
 * @return list<array{label: string, href: string}>
 */
function get_catalog_variants_except_product(int $categoryId, int $productId): array
{
    $currentHref = '/product/' . $productId;

    return array_values(array_filter(
        get_catalog_variants($categoryId),
        static fn(array $variant): bool => $variant['href'] !== $currentHref
    ));
}
