<?php
/**
 * views/product/card.php
 *
 * Контент карточки товара (конфигуратор).
 * Переменная $product приходит из ProductController::getProductById().
 */

declare(strict_types=1);

$optionGroups = [];
foreach ($product['options'] as $opt) {
    $group = $opt['option_group'] ?? 'other';
    $optionGroups[$group][] = $opt;
}

$productImage = product_image($product['image_preview'] ?? null);
?>
<main class="product-page">

    <section class="product-header">
        <nav class="breadcrumb" aria-label="Навигация">
            <a href="/">Главная</a>
            <span aria-hidden="true">/</span>
            <a href="/catalog">Каталог</a>
            <span aria-hidden="true">/</span>
            <a href="/catalog/<?= (int)$product['category_id'] ?>"><?= e($product['category_name']) ?></a>
            <span aria-hidden="true">/</span>
            <span aria-current="page"><?= e($product['sku']) ?></span>
        </nav>

        <h1 class="product-title"><?= e($product['name']) ?></h1>
        <p class="product-subtitle"><?= e($product['description']) ?></p>
    </section>

    <section class="product-layout">

        <div class="product-gallery">
            <img
                id="product-main-image"
                class="gallery__main-image"
                src="<?= e($productImage) ?>"
                alt="<?= e($product['name']) ?>, основное изображение"
                width="680" height="480"
                loading="eager" fetchpriority="high">
        </div>

        <div class="configurator"
             id="product-configurator"
             data-product-id="<?= (int)$product['id'] ?>"
             data-base-price="<?= (float)$product['base_price'] ?>"
             aria-label="Конфигуратор заказа">

            <h2 class="configurator__title">Настройте ваш заказ</h2>

            <?php foreach ($optionGroups as $groupKey => $groupOptions): ?>

                <?php
                $groupType   = $groupOptions[0]['option_type'];
                $groupLabels = [
                    'material' => 'Материал рабочей поверхности',
                    'config'   => 'Конфигурация стола',
                    'extras'   => 'Дополнительное оснащение',
                ];
                $groupLabel = $groupLabels[$groupKey] ?? ucfirst($groupKey);
                ?>

                <fieldset class="config-group" data-group="<?= e($groupKey) ?>">
                    <legend class="config-group__legend"><?= e($groupLabel) ?></legend>
                    <div class="config-options config-options--<?= $groupType === 'select' ? 'radio' : 'checkboxes' ?>">

                        <?php foreach ($groupOptions as $i => $opt): ?>
                            <?php
                            $modifier = (float)$opt['price_modifier'];
                            $priceStr = $modifier > 0
                                ? '+ ' . format_price($modifier)
                                : ($modifier < 0 ? '− ' . format_price(abs($modifier)) : 'Включено');
                            $isFirst  = ($i === 0);
                            ?>

                            <?php if ($groupType === 'select'): ?>
                                <label class="option-card option-card--radio">
                                    <input
                                        type="radio"
                                        name="option_<?= e($groupKey) ?>"
                                        value="<?= (float)$modifier ?>"
                                        data-option-id="<?= (int)$opt['id'] ?>"
                                        data-price-modifier="<?= (float)$modifier ?>"
                                        <?= $isFirst ? 'checked' : '' ?>>
                                    <span class="option-card__body">
                                        <span class="option-card__name"><?= e($opt['option_name']) ?></span>
                                        <span class="option-card__price"><?= e($priceStr) ?></span>
                                    </span>
                                </label>
                            <?php else: ?>
                                <label class="option-card option-card--check">
                                    <input
                                        type="checkbox"
                                        name="option_extras"
                                        value="<?= (float)$modifier ?>"
                                        data-option-id="<?= (int)$opt['id'] ?>"
                                        data-price-modifier="<?= (float)$modifier ?>">
                                    <span class="option-card__body">
                                        <span class="option-card__name"><?= e($opt['option_name']) ?></span>
                                        <span class="option-card__price"><?= e($priceStr) ?></span>
                                    </span>
                                    <span class="option-card__check-icon" aria-hidden="true"></span>
                                </label>
                            <?php endif; ?>

                        <?php endforeach; ?>

                    </div>
                </fieldset>

            <?php endforeach; ?>

            <div class="configurator__summary">
                <div class="price-display" aria-live="polite" aria-atomic="true">
                    <span class="price-display__label">Итоговая стоимость:</span>
                    <span class="price-display__value" id="price-display">
                        <?= e(format_price((float)$product['base_price'])) ?>
                    </span>
                </div>
                <p class="price-display__note">Цена с учётом выбранных опций. НДС включён.</p>

                <button type="button" class="btn btn--primary btn--large" id="btn-order">
                    <span aria-hidden="true">🛒</span> Оформить заказ
                </button>
                <p class="order-hint">Менеджер свяжется с вами в течение 30 минут.</p>
            </div>

        </div>
    </section>

    <?php include __DIR__ . '/modal_order.php'; ?>

    <section class="product-lead">
        <h2 class="product-lead__title">Или оставьте заявку менеджеру</h2>
        <p class="product-lead__text">Подготовим коммерческое предложение без оформления заказа.</p>
        <?php
        $leadSource = 'product';
        $leadCompact = true;
        require __DIR__ . '/../partials/lead-form.php';
        ?>
    </section>

</main>
