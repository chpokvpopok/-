<?php
declare(strict_types=1);

$e = fn(mixed $v): string => htmlspecialchars((string)($v ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
$formatPrice = fn(float $price): string => number_format($price, 0, '.', ' ') . ' ₸';
$locale = $_SESSION['locale'] ?? 'ru';
?>
<!DOCTYPE html>
<html lang="<?= $e($locale) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мебельная платформа — каталог</title>
    <link rel="stylesheet" href="/public/css/configurator.css">
    <style>
        .catalog-grid { display: grid; gap: 24px; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); margin-top: 32px; }
        .catalog-card { background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 24px; text-decoration: none; color: inherit; transition: border-color var(--transition-normal); }
        .catalog-card:hover { border-color: var(--color-accent); }
        .catalog-card h2 { font-size: var(--font-size-lg); margin-bottom: 8px; }
        .catalog-card .price { color: var(--color-accent); font-weight: 600; margin-top: 12px; }
        .page-hero { max-width: 1200px; margin: 0 auto; padding: 48px 24px 0; }
        .page-hero h1 { font-size: var(--font-size-3xl); margin-bottom: 12px; }
        .page-hero p { color: var(--color-text-secondary); }
    </style>
</head>
<body>
<main class="page-hero product-page">
    <h1>Мебельная платформа</h1>
    <p>Профессиональная диспетчерская и технологическая мебель. Выберите товар для настройки конфигурации.</p>

    <?php if (empty($products)): ?>
        <p style="margin-top: 32px; color: var(--color-text-secondary);">Товары пока не добавлены.</p>
    <?php else: ?>
        <div class="catalog-grid">
            <?php foreach ($products as $item): ?>
                <a class="catalog-card" href="/product/<?= (int)$item['id'] ?>">
                    <span style="color: var(--color-text-muted); font-size: var(--font-size-sm);"><?= $e($item['category_name']) ?></span>
                    <h2><?= $e($item['name']) ?></h2>
                    <p style="color: var(--color-text-secondary); font-size: var(--font-size-sm);"><?= $e($item['sku']) ?></p>
                    <p class="price">от <?= $formatPrice((float)$item['base_price']) ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
</body>
</html>
