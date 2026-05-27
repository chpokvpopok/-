<?php
declare(strict_types=1);
$delivery = json_decode($order['delivery_info'] ?? '{}', true) ?: [];
?>
<main class="page-wrap section" style="min-height: calc(100vh - 232px);">
    <div class="container" style="max-width: 680px; text-align: center;">
        <div style="font-size: 4rem; margin-bottom: 16px;" aria-hidden="true">✅</div>

        <h1 class="page-title" style="margin-bottom: 12px;">Заказ №<?= (int)$order['id'] ?> принят!</h1>

        <p class="section__text" style="margin-bottom: 32px;">
            Спасибо, <?= e($delivery['name'] ?? '') ?>!<br>
            Наш менеджер свяжется с вами по номеру
            <strong style="color: var(--color-accent);"><?= e($delivery['phone'] ?? '') ?></strong>
            в течение 30 минут.
        </p>

        <div class="card" style="text-align:left; margin-bottom: 32px;">
            <p class="text-muted" style="margin-bottom: 8px;">ДЕТАЛИ ЗАКАЗА</p>
            <p style="margin-bottom: 12px;">
                <span>Сумма заказа:</span>
                <strong style="float:right; color: var(--color-text);"><?= e(format_price((float)$order['total_price'])) ?></strong>
            </p>
            <p style="margin-bottom: 12px;">
                <span>Адрес доставки:</span>
                <strong style="float:right; color: var(--color-text);">
                    <?= e(($order['delivery_info']['city'] ?? '') . ', ' . ($order['delivery_info']['address'] ?? '')) ?>
                </strong>
            </p>
            <p>
                <span>Статус:</span>
                <strong style="float:right; color: var(--color-success);">Принят в обработку</strong>
            </p>
        </div>

        <a href="/catalog/1" class="btn btn--primary">Продолжить выбор</a>
    </div>
</main>
