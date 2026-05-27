<?php
/**
 * views/product/card.php
 *
 * Серверный шаблон карточки товара.
 * Переменная $product приходит из ProductController::getProductById().
 *
 * Весь вывод экранируется через htmlspecialchars() — защита от XSS.
 * Цена и опции группируются здесь для удобства шаблона.
 */

declare(strict_types=1);

// Вспомогательная функция экранирования для этого шаблона
$e = fn(mixed $v): string => htmlspecialchars((string)($v ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');

// Форматирование цены
$formatPrice = fn(float $price): string =>
    number_format($price, 0, '.', ' ') . ' ₸';

// Группируем опции по option_group для удобного вывода
$optionGroups = [];
foreach ($product['options'] as $opt) {
    $group = $opt['option_group'] ?? 'other';
    $optionGroups[$group][] = $opt;
}
?>
<!DOCTYPE html>
<html lang="<?= $e($_SESSION['locale'] ?? 'ru') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $e($product['name']) ?> — Мебельная платформа</title>
    <meta name="description" content="<?= $e(mb_substr(strip_tags($product['description'] ?? ''), 0, 160)) ?>">
    <link rel="stylesheet" href="/public/css/configurator.css">
    <link rel="preload" href="<?= $e($product['image_preview'] ?? '/public/images/placeholder.webp') ?>" as="image" type="image/webp">
</head>
<body>

<main class="product-page">

    <section class="product-header">
        <nav class="breadcrumb" aria-label="Навигация">
            <a href="/">Главная</a>
            <span aria-hidden="true">/</span>
            <a href="/catalog/<?= $e($product['category_id']) ?>"><?= $e($product['category_name']) ?></a>
            <span aria-hidden="true">/</span>
            <span aria-current="page"><?= $e($product['sku']) ?></span>
        </nav>

        <h1 class="product-title"><?= $e($product['name']) ?></h1>
        <p class="product-subtitle"><?= $e($product['description']) ?></p>
    </section>

    <section class="product-layout">

        <!-- Галерея -->
        <div class="product-gallery">
            <img
                id="product-main-image"
                class="gallery__main-image"
                src="<?= $e($product['image_preview'] ?? '/public/images/placeholder.webp') ?>"
                alt="<?= $e($product['name']) ?>, основное изображение"
                width="680" height="480"
                loading="eager" fetchpriority="high">
        </div>

        <!-- Конфигуратор: data-атрибуты несут данные для JS-модуля -->
        <div class="configurator"
             id="product-configurator"
             data-product-id="<?= (int)$product['id'] ?>"
             data-base-price="<?= (float)$product['base_price'] ?>"
             aria-label="Конфигуратор заказа">

            <h2 class="configurator__title">Настройте ваш заказ</h2>

            <?php foreach ($optionGroups as $groupKey => $groupOptions): ?>

                <?php
                // Определяем тип группы по первой опции
                $groupType    = $groupOptions[0]['option_type'];
                // Человекочитаемое название группы
                $groupLabels  = [
                    'material' => 'Материал рабочей поверхности',
                    'config'   => 'Конфигурация стола',
                    'extras'   => 'Дополнительное оснащение',
                ];
                $groupLabel = $groupLabels[$groupKey] ?? ucfirst($groupKey);
                ?>

                <fieldset class="config-group" data-group="<?= $e($groupKey) ?>">
                    <legend class="config-group__legend"><?= $e($groupLabel) ?></legend>
                    <div class="config-options config-options--<?= $groupType === 'select' ? 'radio' : 'checkboxes' ?>">

                        <?php foreach ($groupOptions as $i => $opt): ?>
                            <?php
                            $modifier = (float)$opt['price_modifier'];
                            $priceStr = $modifier > 0
                                ? '+ ' . $formatPrice($modifier)
                                : ($modifier < 0 ? '− ' . $formatPrice(abs($modifier)) : 'Включено');
                            $isFirst  = ($i === 0);
                            ?>

                            <?php if ($groupType === 'select'): ?>
                                <!-- Radio (взаимоисключающий выбор) -->
                                <label class="option-card option-card--radio">
                                    <input
                                        type="radio"
                                        name="option_<?= $e($groupKey) ?>"
                                        value="<?= (float)$modifier ?>"
                                        data-option-id="<?= (int)$opt['id'] ?>"
                                        data-price-modifier="<?= (float)$modifier ?>"
                                        <?= $isFirst ? 'checked' : '' ?>>
                                    <span class="option-card__body">
                                        <span class="option-card__name"><?= $e($opt['option_name']) ?></span>
                                        <span class="option-card__price"><?= $e($priceStr) ?></span>
                                    </span>
                                </label>

                            <?php else: ?>
                                <!-- Checkbox (независимый выбор) -->
                                <label class="option-card option-card--check">
                                    <input
                                        type="checkbox"
                                        name="option_extras"
                                        value="<?= (float)$modifier ?>"
                                        data-option-id="<?= (int)$opt['id'] ?>"
                                        data-price-modifier="<?= (float)$modifier ?>">
                                    <span class="option-card__body">
                                        <span class="option-card__name"><?= $e($opt['option_name']) ?></span>
                                        <span class="option-card__price"><?= $e($priceStr) ?></span>
                                    </span>
                                    <span class="option-card__check-icon" aria-hidden="true"></span>
                                </label>
                            <?php endif; ?>

                        <?php endforeach; ?>

                    </div>
                </fieldset>

            <?php endforeach; ?>

            <!-- Итог и кнопка заказа -->
            <div class="configurator__summary">
                <div class="price-display" aria-live="polite" aria-atomic="true">
                    <span class="price-display__label">Итоговая стоимость:</span>
                    <span class="price-display__value" id="price-display">
                        <?= $e($formatPrice((float)$product['base_price'])) ?>
                    </span>
                </div>
                <p class="price-display__note">Цена с учётом выбранных опций. НДС включён.</p>

                <button type="button" class="btn btn--primary btn--large" id="btn-order">
                    <span aria-hidden="true">🛒</span> Оформить заказ
                </button>
                <p class="order-hint">Менеджер свяжется с вами в течение 30 минут.</p>
            </div>

        </div><!-- /.configurator -->
    </section>

    <!-- Модальное окно (берём из статического шаблона — оно одинаково) -->
    <?php include __DIR__ . '/modal_order.php'; ?>

</main>

<script src="/public/js/configurator.js" defer type="module"></script>
</body>
</html>
