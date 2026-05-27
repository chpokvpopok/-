<?php
declare(strict_types=1);
?>
<main class="page-home">
    <?php include __DIR__ . '/partials/sections/hero.php'; ?>
    <?php include __DIR__ . '/partials/sections/directions.php'; ?>

    <section class="section section--sm">
        <div class="container">
            <h2 class="section__title">Популярные модели</h2>
            <?php if (empty($products)): ?>
                <p class="section__text">Пока нет доступных товаров.</p>
            <?php else: ?>
                <div class="home-grid">
                    <?php foreach ($products as $item): ?>
                        <article class="card home-card">
                            <div>
                                <p class="text-muted"><?= e($item['category_name']) ?></p>
                                <h3><?= e($item['name']) ?></h3>
                                <p class="text-muted"><?= e($item['sku']) ?></p>
                            </div>
                            <div>
                                <p class="text-muted" style="margin-bottom: 16px; font-weight: 600; color: var(--color-accent);">
                                    от <?= format_price((float)$item['base_price']) ?>
                                </p>
                                <a class="btn btn--outline" href="/product/<?= (int)$item['id'] ?>">Настроить</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include __DIR__ . '/partials/sections/advantages.php'; ?>
    <?php include __DIR__ . '/partials/sections/cases-static.php'; ?>
    <?php include __DIR__ . '/partials/sections/lead.php'; ?>
    <?php include __DIR__ . '/partials/sections/faq.php'; ?>
</main>
