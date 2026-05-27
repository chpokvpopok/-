<?php
/**
 * Карточка направления с вариантами.
 *
 * @var array{
 *     id: int,
 *     title: string,
 *     description: string,
 *     variants: list<array{label: string, href: string}>
 * } $direction
 */
declare(strict_types=1);
?>
<article class="card direction-card">
    <h3><?= e($direction['title']) ?></h3>
    <p class="text-muted"><?= e($direction['description']) ?></p>
    <?php
    $variants = $direction['variants'];
    require __DIR__ . '/variants-list.php';
    ?>
    <a class="btn btn--outline" href="/catalog/<?= (int)$direction['id'] ?>">Перейти</a>
</article>
