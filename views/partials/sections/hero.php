<?php
/**
 * views/partials/sections/hero.php
 * Переориентирован на домашнюю мебель с правильной иерархией:
 * 1. На переднем плане (h1) — то о чём сайт (Домашняя мебель)
 * 2. На втором плане (слоган) — description с уточнением бренда
 */
declare(strict_types=1);
?>
<section class="section home-hero">
    <div class="container home-hero__inner">
        <div class="home-hero__content">
            <!-- Главное: то о чём сайт -->
            <h1>Стильная домашняя мебель, созданная для вашего комфорта</h1>
            
            <!-- Второй план: слоган бренда -->
            <p class="home-hero__tagline">Quattro — мебель, которая превращает дом в идеальное пространство для жизни</p>
            
            <!-- Развёрнутое описание -->
            <p class="section__text">Мы создаём красивую, функциональную и долговечную мебель для спальни, гостиной, кухни и офис-пространства в доме. Каждое изделие разработано с вниманием к деталям, дизайну и вашему образу жизни.</p>
            
            <div class="home-hero-actions">
                <a class="btn btn--primary" href="/catalog">Смотреть каталог</a>
                <a class="btn btn--outline" href="#lead-form">Запросить консультацию</a>
                <a class="btn btn--outline" href="/product/1">Конфигуратор</a>
            </div>
        </div>
    </div>
</section>
