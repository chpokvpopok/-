<?php
/**
 * Список вариантов мебели.
 *
 * @var list<array{label: string, href: string}> $variants
 * @var string|null $title
 * @var string|null $listClass
 */
declare(strict_types=1);

if (empty($variants)) {
    return;
}

$listClass = $listClass ?? 'variants-list';
?>
<?php if (!empty($title)): ?>
    <p class="variants-list__title"><?= e($title) ?></p>
<?php endif; ?>
<ul class="<?= e($listClass) ?>">
    <?php foreach ($variants as $variant): ?>
        <li>
            <a href="<?= e($variant['href']) ?>"><?= e($variant['label']) ?></a>
        </li>
    <?php endforeach; ?>
</ul>
