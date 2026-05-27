<?php
declare(strict_types=1);
?>
<main class="page-wrap section">
    <div class="container">
        <nav class="breadcrumb" aria-label="Навигация">
            <a href="/">Главная</a>
            <span aria-hidden="true">/</span>
            <a href="/catalog">Каталог</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page"><?= e($category['name']) ?></span>
        </nav>

        <h1 class="page-title"><?= e($category['name']) ?></h1>

        <?php if (empty($products)): ?>
            <p class="section__text">В этой категории пока нет товаров.</p>
        <?php else: ?>
            <div class="grid-3">
                <?php foreach ($products as $item): ?>
                    <article class="card catalog-card">
                        <img
                            class="catalog-card__image"
                            src="<?= e(product_image($item['image_preview'] ?? null)) ?>"
                            alt="<?= e($item['name']) ?>"
                            width="360"
                            height="240"
                            loading="lazy">
                        <h2><?= e($item['name']) ?></h2>
                        <p class="text-muted"><?= e($item['sku']) ?></p>
                        <p class="catalog-card__price">от <?= format_price((float)$item['base_price']) ?></p>
                        <a class="btn btn--primary" href="/product/<?= (int)$item['id'] ?>">Настроить</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>
