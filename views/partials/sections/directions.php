<?php
/**
 * views/partials/sections/directions.php
 */
declare(strict_types=1);
?>
<section id="products" class="section section--sm">
    <div class="container">
        <h2 class="section__title">Наши направления</h2>
        <p class="section__text">Коллекции Quattro для каждой комнаты дома — с возможностью подобрать материалы и комплектацию под ваш интерьер.</p>
        <?php $homeDirections = get_home_catalog_directions($categories ?? [], $products ?? []); ?>
        <?php if (empty($homeDirections)): ?>
            <p class="section__text">Категории загружаются из каталога. Перейдите в <a href="/catalog">общий каталог</a> или в <a href="#configurator-models">конфигуратор</a>.</p>
        <?php else: ?>
            <div class="grid-3">
                <?php foreach ($homeDirections as $direction): ?>
                    <?php require __DIR__ . '/../direction-card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
