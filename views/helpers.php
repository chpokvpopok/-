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
