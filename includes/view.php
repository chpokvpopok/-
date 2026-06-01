<?php

declare(strict_types=1);

require_once __DIR__ . '/../views/helpers.php';

/**
 * Рендерит view через общий layout.
 *
 * @param string $view Путь к шаблону относительно views/, без расширения
 * @param array<string,mixed> $data Данные для шаблона
 * @param array<string,mixed> $layout Дополнительные параметры layout
 */
function render(string $view, array $data = [], array $layout = []): void
{
    $viewFile = __DIR__ . '/../views/' . ltrim($view, '/\\') . '.php';

    if (!file_exists($viewFile)) {
        throw new RuntimeException('View not found: ' . $viewFile);
    }

    extract($data, EXTR_SKIP);

    $pageTitle       = $layout['pageTitle'] ?? $data['pageTitle'] ?? 'Quattro';
    $pageDescription = $layout['pageDescription'] ?? $data['pageDescription'] ?? 'Quattro - стильная домашняя мебель с онлайн-конфигуратором.';
    $bodyClass       = $layout['bodyClass'] ?? $data['bodyClass'] ?? '';
    $extraCss        = $layout['extraCss'] ?? $data['extraCss'] ?? [];
    $extraJs         = $layout['extraJs'] ?? $data['extraJs'] ?? [];

    ob_start();
    require $viewFile;
    $content = ob_get_clean();

    require __DIR__ . '/../views/layouts/main.php';
}
