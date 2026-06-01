<?php
/**
 * Карточка направления с вариантами.
 *
 * @var array{
 *     id: int,
 *     slug: string,
 *     title: string,
 *     description: string,
 *     variants: list<array{label: string, href: string}>
 * } $direction
 */
declare(strict_types=1);
?>
<article class="card direction-card catalog-card">
    <img
        class="catalog-card__image"
        src="<?= e(category_image($direction['slug'])) ?>"
        alt="<?= e($direction['title']) ?>"
        width="360"
        height="240"
        loading="lazy">
    <h3><?= e($direction['title']) ?></h3>
    <p class="text-muted"><?= e($direction['description']) ?></p>
    <?php
    $variants = $direction['variants'];
    require __DIR__ . '/variants-list.php';
    ?>
    <a class="btn btn--outline" href="/catalog/<?= (int)$direction['id'] ?>">Перейти</a>
</article>
