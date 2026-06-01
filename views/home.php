<?php
declare(strict_types=1);
?>
<?php
$configurableProducts = filter_configurable_products($products ?? []);
?>
<main class="page-home">
    <?php include __DIR__ . '/partials/sections/hero.php'; ?>
    <?php
    include __DIR__ . '/partials/sections/configurator-carousel.php';
    ?>
    <?php include __DIR__ . '/partials/sections/directions.php'; ?>
    <?php include __DIR__ . '/partials/sections/advantages.php'; ?>
    <?php include __DIR__ . '/partials/sections/cases-static.php'; ?>
    <?php include __DIR__ . '/partials/sections/lead.php'; ?>
    <?php include __DIR__ . '/partials/sections/faq.php'; ?>
</main>
