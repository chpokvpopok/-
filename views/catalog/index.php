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
                    <article class="card catalog-card">
                        <h2><?= e($category['name']) ?></h2>
                        <p class="text-muted">
                            <?= (int)$category['product_count'] ?>
                            <?= ((int)$category['product_count'] === 1) ? 'модель' : 'моделей' ?>
                        </p>
                        <a class="btn btn--outline" href="/catalog/<?= (int)$category['id'] ?>">Смотреть</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>
