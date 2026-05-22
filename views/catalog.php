<?php
declare(strict_types=1);

$e = fn(mixed $v): string => htmlspecialchars((string)($v ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
$formatPrice = fn(float $price): string => number_format($price, 0, '.', ' ') . ' ₸';
$locale = $_SESSION['locale'] ?? 'ru';
$categoryTitle = !empty($products[0]['category_name'])
    ? $products[0]['category_name']
    : 'Каталог';
?>
<!DOCTYPE html>
<html lang="<?= $e($locale) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $e($categoryTitle) ?> — каталог</title>
    <link rel="stylesheet" href="/public/css/configurator.css">
    <style>
        .catalog-grid { display: grid; gap: 24px; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); margin-top: 32px; }
        .catalog-card { background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 24px; text-decoration: none; color: inherit; }
        .catalog-card:hover { border-color: var(--color-accent); }
        .page-wrap { max-width: 1200px; margin: 0 auto; padding: 48px 24px; }
    </style>
</head>
<body>
<main class="product-page page-wrap">
    <nav class="breadcrumb" aria-label="Навигация">
        <a href="/">Главная</a>
        <span aria-hidden="true">/</span>
        <span aria-current="page"><?= $e($categoryTitle) ?></span>
    </nav>

    <h1 style="margin-top: 16px; font-size: var(--font-size-2xl);"><?= $e($categoryTitle) ?></h1>

    <?php if (empty($products)): ?>
        <p style="margin-top: 24px; color: var(--color-text-secondary);">В этой категории пока нет товаров.</p>
    <?php else: ?>
        <div class="catalog-grid">
            <?php foreach ($products as $item): ?>
                <a class="catalog-card" href="/product/<?= (int)$item['id'] ?>">
                    <h2 style="font-size: var(--font-size-lg); margin-bottom: 8px;"><?= $e($item['name']) ?></h2>
                    <p style="color: var(--color-text-secondary); font-size: var(--font-size-sm);"><?= $e($item['sku']) ?></p>
                    <p style="color: var(--color-accent); font-weight: 600; margin-top: 12px;">от <?= $formatPrice((float)$item['base_price']) ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
</body>
</html>
