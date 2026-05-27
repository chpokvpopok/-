<?php
declare(strict_types=1);
?>
<main class="page-wrap section">
    <div class="container">
        <nav class="breadcrumb" aria-label="Навигация">
            <a href="/">Главная</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page"><?= e($categoryTitle) ?></span>
        </nav>

        <h1 class="page-title"><?= e($categoryTitle) ?></h1>

        <?php if (empty($products)): ?>
            <p class="section__text">В этой категории пока нет товаров.</p>
        <?php else: ?>
            <div class="grid-3">
                <?php foreach ($products as $item): ?>
                    <a class="card" href="/product/<?= (int)$item['id'] ?>">
                        <h2><?= e($item['name']) ?></h2>
                        <p class="text-muted"><?= e($item['sku']) ?></p>
                        <p style="margin-top: 16px; color: var(--color-accent); font-weight: 600;">от <?= format_price((float)$item['base_price']) ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>
