<?php
declare(strict_types=1);

$variants = get_catalog_variants_for_category($category, $products ?? []);
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

        <?php if (!empty($variants)): ?>
            <section id="variants" class="catalog-variants">
                <?php
                $title = 'Варианты мебели';
                require __DIR__ . '/../partials/variants-list.php';
                ?>
            </section>
        <?php endif; ?>

        <?php if (empty($products)): ?>
            <p class="section__text">В этой категории пока нет активных моделей. Откройте <a href="/catalog">общий каталог</a>.</p>
            <a class="btn btn--primary" href="/catalog">← К каталогу</a>
        <?php else: ?>
            <h2 class="catalog-section-title">Модели с конфигуратором</h2>
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
                        <h3><?= e($item['name']) ?></h3>
                        <p class="catalog-card__price">от <?= format_price((float)$item['base_price']) ?></p>
                        <a class="btn btn--primary" href="<?= e(product_href($item)) ?>">
                            <?= !empty($item['options']) ? 'Настроить' : 'Подробнее' ?>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>
