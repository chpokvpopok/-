<?php
declare(strict_types=1);
?>
<main class="page-wrap section">
    <div class="container">
        <nav class="breadcrumb" aria-label="Навигация">
            <a href="/">Главная</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page">Каталог</span>
        </nav>

        <h1 class="page-title">Каталог</h1>
        <p class="section__text">Выберите комнату - от спальни и гостиной до кухни и домашнего офиса.</p>

        <?php if (empty($categories)): ?>
            <p class="section__text">Категории пока не добавлены.</p>
        <?php else: ?>
            <div class="grid-3">
                <?php foreach ($categories as $category): ?>
                    <?php
                    $direction  = get_catalog_direction_for_category($category, $products ?? []);
                    ?>
                    <article class="card catalog-card direction-card">
                        <img
                            class="catalog-card__image"
                            src="<?= e(category_image((string)($category['slug'] ?? ''))) ?>"
                            alt="<?= e($category['name']) ?>"
                            width="360"
                            height="240"
                            loading="lazy">
                        <h2><?= e($category['name']) ?></h2>
                        <?php if ($direction !== null): ?>
                            <?php
                            $variants = $direction['variants'];
                            $title = 'Варианты мебели';
                            require __DIR__ . '/../partials/variants-list.php';
                            ?>
                        <?php else: ?>
                            <p class="text-muted">
                                <?= (int)$category['product_count'] ?>
                                <?= ((int)$category['product_count'] === 1) ? 'модель' : 'моделей' ?>
                            </p>
                        <?php endif; ?>
                        <a class="btn btn--outline" href="/catalog/<?= (int)$category['id'] ?>">Смотреть</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>
