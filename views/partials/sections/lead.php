<?php
/**
 * views/partials/sections/lead.php
 */
declare(strict_types=1);

$leadSource = 'home';
?>
<section id="lead-form" class="section">
    <div class="container">
        <div class="card">
            <h2 class="section__title">Рассчитать ваш проект</h2>
            <p class="section__text">Оставьте заявку - подготовим коммерческое предложение под ваши задачи.</p>
            <?php require __DIR__ . '/../lead-form.php'; ?>
        </div>
    </div>
</section>
