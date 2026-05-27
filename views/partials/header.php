<?php
/**
 * views/partials/header.php
 */
declare(strict_types=1);

require __DIR__ . '/contacts.php';
?>
<header class="site-header">
    <div class="container">
        <a class="site-logo" href="/">Quattro</a>

        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-navigation">
            <span class="nav-toggle__bar"></span>
            <span class="screen-reader-only">Открыть меню</span>
        </button>

        <nav id="site-navigation" class="site-nav" aria-label="Главное меню">
            <a class="site-nav__link" href="/">Главная</a>
            <a class="site-nav__link" href="/catalog">Каталог</a>
            <a class="site-nav__link" href="/#cases">Кейсы</a>
            <a class="site-nav__link" href="/product/1">Конфигуратор</a>
        </nav>

        <div class="site-contact">
            <a class="site-nav__link" href="<?= e($contactPhoneHref) ?>"><?= e($contactPhone) ?></a>
            <a class="site-nav__link" href="mailto:<?= e($contactEmail) ?>"><?= e($contactEmail) ?></a>
        </div>

        <div class="site-actions">
            <a class="btn btn--primary" href="/catalog">Каталог</a>
        </div>
    </div>
</header>
