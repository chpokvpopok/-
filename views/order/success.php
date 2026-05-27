<?php
declare(strict_types=1);

$delivery = is_array($order['delivery_info'] ?? null)
    ? $order['delivery_info']
    : (json_decode($order['delivery_info'] ?? '{}', true) ?: []);
?>
<main class="page-order-success section">
    <div class="container page-order-success__inner">
        <div class="page-order-success__icon" aria-hidden="true">✅</div>

        <h1 class="section__title">Заказ №<?= (int)$order['id'] ?> принят!</h1>

        <p class="section__text page-order-success__lead">
            Спасибо, <?= e($delivery['name'] ?? '') ?>!<br>
            Наш менеджер свяжется с вами по номеру
            <strong><?= e($delivery['phone'] ?? '') ?></strong>
            в течение 30 минут.
        </p>

        <div class="card page-order-success__details">
            <p class="text-muted page-order-success__label">Детали заказа</p>
            <dl class="page-order-success__list">
                <div class="page-order-success__row">
                    <dt>Сумма заказа</dt>
                    <dd><?= e(format_price((float)$order['total_price'])) ?></dd>
                </div>
                <div class="page-order-success__row">
                    <dt>Адрес доставки</dt>
                    <dd><?= e(trim(($delivery['city'] ?? '') . ', ' . ($delivery['address'] ?? ''), ', ')) ?></dd>
                </div>
                <div class="page-order-success__row">
                    <dt>Статус</dt>
                    <dd class="page-order-success__status">Принят в обработку</dd>
                </div>
            </dl>
        </div>

        <div class="page-order-success__actions">
            <a href="/catalog" class="btn btn--primary">В каталог</a>
            <a href="/" class="btn btn--outline">На главную</a>
        </div>
    </div>
</main>
