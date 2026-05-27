<?php
/**
 * views/partials/sections/directions.php
 */
declare(strict_types=1);
?>
<section id="products" class="section section--sm">
    <div class="container">
        <h2 class="section__title">Наши направления</h2>
        <p class="section__text">Готовые решения Quattro для диспетчерских, офисов и модульных пространств — с возможностью настройки под ваш проект.</p>
        <div class="grid-3">
            <?php foreach (get_catalog_directions() as $direction): ?>
                <?php require __DIR__ . '/../direction-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
