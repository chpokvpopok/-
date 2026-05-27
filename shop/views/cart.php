<?php
declare(strict_types=1);

$e = fn(mixed $v): string => htmlspecialchars((string)($v ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
$locale = $_SESSION['locale'] ?? 'ru';
?>
<!DOCTYPE html>
<html lang="<?= $e($locale) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина</title>
    <link rel="stylesheet" href="/public/css/configurator.css">
</head>
<body>
<main class="product-page" style="max-width: 720px; margin: 0 auto; padding: 48px 24px; text-align: center;">
    <h1 style="font-size: var(--font-size-2xl); margin-bottom: 16px;">Корзина</h1>
    <p style="color: var(--color-text-secondary); margin-bottom: 32px;">
        Оформление заказа выполняется через конфигуратор на странице товара.
    </p>
    <a href="/" class="btn btn--primary" style="display: inline-flex;">← В каталог</a>
</main>
</body>
</html>
