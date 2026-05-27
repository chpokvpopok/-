<?php
/**
 * views/order/success.php
 * Страница подтверждения заказа.
 * Переменная $order приходит из OrderController::showSuccess()
 */
declare(strict_types=1);
$e = fn(mixed $v): string => htmlspecialchars((string)($v ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');

$delivery = json_decode($order['delivery_info'] ?? '{}', true);
$formatPrice = fn(float $p): string => number_format($p, 0, '.', ' ') . ' ₸';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Заказ №<?= (int)$order['id'] ?> принят</title>
    <link rel="stylesheet" href="/public/css/configurator.css">
</head>
<body>
<main class="product-page" style="max-width: 600px; text-align: center; padding-top: 60px;">

    <div style="font-size: 4rem; margin-bottom: 16px;" aria-hidden="true">✅</div>

    <h1 class="product-title" style="margin-bottom: 12px;">
        Заказ №<?= (int)$order['id'] ?> принят!
    </h1>

    <p style="color: var(--color-text-secondary); font-size: 1.125rem; margin-bottom: 32px;">
        Спасибо, <?= $e($delivery['name'] ?? '') ?>!<br>
        Наш менеджер свяжется с вами по номеру
        <strong style="color: var(--color-text-primary);"><?= $e($delivery['phone'] ?? '') ?></strong>
        в течение 30 минут.
    </p>

    <div style="background: var(--color-surface); border: 1px solid var(--color-border);
                border-radius: var(--radius-lg); padding: 24px; text-align: left;
                margin-bottom: 32px;">
        <p style="color: var(--color-text-muted); font-size: 0.875rem; margin-bottom: 8px;">
            ДЕТАЛИ ЗАКАЗА
        </p>
        <p style="margin-bottom: 6px;">
            <span style="color: var(--color-text-secondary);">Сумма заказа:</span>
            <strong style="color: var(--color-text-primary); float: right;">
                <?= $e($formatPrice((float)$order['total_price'])) ?>
            </strong>
        </p>
        <p style="margin-bottom: 6px;">
            <span style="color: var(--color-text-secondary);">Адрес доставки:</span>
            <span style="color: var(--color-text-primary); float: right;">
                <?= $e(($delivery['city'] ?? '') . ', ' . ($delivery['address'] ?? '')) ?>
            </span>
        </p>
        <p>
            <span style="color: var(--color-text-secondary);">Статус:</span>
            <span style="color: var(--color-success); float: right; font-weight: 600;">
                Принят в обработку
            </span>
        </p>
    </div>

    <a href="/catalog/1" class="btn btn--primary" style="display:inline-flex;">
        Продолжить выбор
    </a>

</main>
</body>
</html>
