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
