<?php
/**
 * Сетка всех моделей с конфигуратором.
 *
 * @var list<array<string, mixed>> $configurableProducts
 * @var string $currentProductSlug Slug текущей модели (подсветка карточки)
 */
declare(strict_types=1);

$configurableProducts = $configurableProducts ?? [];
$currentProductSlug   = $currentProductSlug ?? '';

if ($configurableProducts === []) {
    return;
}
?>
<section class="section section--sm configurator-models-grid" aria-labelledby="configurator-models-grid-title">
    <div class="container">
        <h2 id="configurator-models-grid-title" class="section__title">Все модели конфигуратора</h2>
        <p class="section__text">Выберите другую модель — настройки и цена откроются на отдельной странице.</p>
        <div class="home-grid">
            <?php foreach ($configurableProducts as $item): ?>
                <?php $isCurrent = ($item['slug'] ?? '') === $currentProductSlug; ?>
                <article class="card home-card catalog-card<?= $isCurrent ? ' home-card--current' : '' ?>">
                    <img
                        class="catalog-card__image"
                        src="<?= e(product_image($item['image_preview'] ?? null)) ?>"
                        alt="<?= e($item['name']) ?>"
                        width="360"
                        height="240"
                        loading="lazy">
                    <div>
                        <p class="text-muted"><?= e($item['category_name']) ?></p>
                        <h3><?= e($item['name']) ?></h3>
                        <?php if ($isCurrent): ?>
                            <p class="text-muted" style="color: var(--color-accent); font-weight: 600;">Сейчас настраиваете</p>
                        <?php endif; ?>
                        <?php $product = $item; require __DIR__ . '/../configurator-preview-groups.php'; ?>
                    </div>
                    <div>
                        <p class="text-muted" style="margin-bottom: 16px; font-weight: 600; color: var(--color-accent);">
                            от <?= format_price((float)$item['base_price']) ?>
                        </p>
                        <?php if ($isCurrent): ?>
                            <span class="btn btn--outline" aria-current="page">Текущая модель</span>
                        <?php else: ?>
                            <a class="btn btn--outline" href="<?= e(product_href($item)) ?>">Настроить</a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
