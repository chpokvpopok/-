<?php
declare(strict_types=1);

require __DIR__ . '/../partials/contacts.php';
?>
<main class="page-wrap section">
    <div class="container">
        <nav class="breadcrumb" aria-label="Навигация">
            <a href="/">Главная</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page">Политика конфиденциальности</span>
        </nav>

        <h1 class="page-title">Политика конфиденциальности</h1>

        <div class="card" style="padding: 28px 32px;">
            <p class="section__text">
                Quattro обрабатывает персональные данные, которые вы указываете в формах заявки и заказа
                (имя, телефон, email, адрес доставки), только для связи с вами и подготовки коммерческого предложения.
            </p>
            <p class="section__text">
                Данные не передаются третьим лицам без вашего согласия, за исключением случаев, предусмотренных
                законодательством Республики Казахстан.
            </p>
            <p class="section__text">
                По вопросам хранения и удаления данных обращайтесь:
                <a href="mailto:<?= e($contactEmail) ?>"><?= e($contactEmail) ?></a>.
            </p>
        </div>

        <p style="margin-top: 24px;">
            <a href="/" class="btn btn--outline">← На главную</a>
        </p>
    </div>
</main>
