<?php
/**
 * Карусель моделей с онлайн-конфигуратором.
 *
 * @var list<array<string, mixed>> $configurableProducts
 */
declare(strict_types=1);

$configurableProducts = $configurableProducts ?? [];
if ($configurableProducts === []) {
    return;
}
?>
<section id="configurator-models" class="section section--sm models-carousel-section" aria-labelledby="configurator-models-title">
    <div class="container">
        <h2 id="configurator-models-title" class="section__title">Конфигуратор моделей</h2>
        <p class="section__text">Выберите мебель, настройте материалы и комплектацию - цена пересчитается сразу.</p>

        <div class="models-carousel" data-models-carousel>
            <button type="button" class="models-carousel__nav models-carousel__nav--prev" aria-label="Предыдущая модель" data-carousel-prev>‹</button>

            <div class="models-carousel__viewport">
                <div class="models-carousel__track" data-carousel-track>
                    <?php foreach ($configurableProducts as $index => $item): ?>
                        <article
                            class="models-carousel__slide card catalog-card<?= $index === 0 ? ' is-active' : '' ?>"
                            data-carousel-slide
                            aria-hidden="<?= $index === 0 ? 'false' : 'true' ?>">
                            <img
                                class="catalog-card__image"
                                src="<?= e(product_image($item['image_preview'] ?? null)) ?>"
                                alt="<?= e($item['name']) ?>"
                                width="480"
                                height="320"
                                loading="<?= $index === 0 ? 'eager' : 'lazy' ?>">
                            <p class="text-muted"><?= e($item['category_name']) ?></p>
                            <h3><?= e($item['name']) ?></h3>
                            <?php $product = $item; require __DIR__ . '/../configurator-preview-groups.php'; ?>
                            <p class="catalog-card__price">от <?= format_price((float)$item['base_price']) ?></p>
                            <a class="btn btn--primary" href="<?= e(product_href($item)) ?>">Настроить в конфигураторе</a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="button" class="models-carousel__nav models-carousel__nav--next" aria-label="Следующая модель" data-carousel-next>›</button>

            <div class="models-carousel__dots" role="tablist" aria-label="Модели конфигуратора">
                <?php foreach ($configurableProducts as $index => $item): ?>
                    <button
                        type="button"
                        class="models-carousel__dot<?= $index === 0 ? ' is-active' : '' ?>"
                        role="tab"
                        aria-label="<?= e($item['name']) ?>"
                        aria-selected="<?= $index === 0 ? 'true' : 'false' ?>"
                        data-carousel-dot="<?= $index ?>"></button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
