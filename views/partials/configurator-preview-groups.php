<?php
/**
 * Список групп вариантов конфигуратора для товара.
 *
 * @var array<string, mixed> $product
 */
declare(strict_types=1);

$previewGroups = product_configurator_preview_groups($product ?? []);
if ($previewGroups === []) {
    return;
}
?>
<ul class="config-preview-groups">
    <?php foreach ($previewGroups as $group): ?>
        <li class="config-preview-groups__item">
            <span class="config-preview-groups__title"><?= e($group['title']) ?>:</span>
            <?= e(implode(', ', $group['values'])) ?>
        </li>
    <?php endforeach; ?>
</ul>
