<?php
/**
 * Минимальная замена mb_* для UTF-8, если расширение mbstring не включено в php.ini (часто на Windows).
 */

declare(strict_types=1);

if (!function_exists('mb_strlen')) {
    function mb_strlen(string $string, ?string $encoding = null): int
    {
        if ($encoding !== null && strtoupper($encoding) !== 'UTF-8') {
            return strlen($string);
        }

        if ($string === '') {
            return 0;
        }

        return preg_match_all('/./u', $string);
    }
}

if (!function_exists('mb_substr')) {
    function mb_substr(string $string, int $start, ?int $length = null, ?string $encoding = null): string
    {
        if ($encoding !== null && strtoupper($encoding) !== 'UTF-8') {
            return $length === null
                ? substr($string, $start)
                : substr($string, $start, $length);
        }

        if ($string === '') {
            return '';
        }

        if (function_exists('iconv_substr')) {
            $result = iconv_substr($string, $start, $length ?? PHP_INT_MAX, 'UTF-8');

            return $result === false ? '' : $result;
        }

        preg_match_all('/./u', $string, $matches);
        $chars = $matches[0] ?? [];

        if ($start < 0) {
            $start = max(0, count($chars) + $start);
        }

        $slice = array_slice($chars, $start, $length);

        return implode('', $slice);
    }
}
