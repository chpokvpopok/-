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
        <p class="section__text">Выберите направление — от диспетчерских пультов до модульных систем.</p>

        <?php if (empty($categories)): ?>
            <p class="section__text">Категории пока не добавлены.</p>
        <?php else: ?>
            <div class="grid-3">
                <?php foreach ($categories as $category): ?>
                    <?php
                    $categoryId = (int)$category['id'];
                    $direction  = get_catalog_direction($categoryId);
                    ?>
                    <article class="card catalog-card direction-card">
                        <h2><?= e($category['name']) ?></h2>
                        <?php if ($direction !== null): ?>
                            <p class="text-muted"><?= e($direction['description']) ?></p>
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
                        <a class="btn btn--outline" href="/catalog/<?= $categoryId ?>">Смотреть</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>
