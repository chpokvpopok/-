<?php
/**
 * views/partials/lead-form.php
 *
 * Переменные (опционально):
 *   $leadSource  - home|product|cases|contacts (по умолчанию home)
 *   $leadCompact - true для компактной версии на странице товара
 */

declare(strict_types=1);

$leadSource  = $leadSource ?? 'home';
$leadCompact = !empty($leadCompact);
$formClass   = 'lead-form js-lead-form' . ($leadCompact ? ' lead-form--compact' : '');
?>
<form class="<?= e($formClass) ?>" action="#" method="post" novalidate>
    <input type="hidden" name="source" value="<?= e($leadSource) ?>">

    <div class="lead-form-grid">
        <label>
            Имя <span aria-hidden="true">*</span>
            <input type="text" name="name" placeholder="Иван Иванов" required autocomplete="name">
        </label>
        <label>
            Email <span aria-hidden="true">*</span>
            <input type="email" name="email" placeholder="example@mail.ru" required autocomplete="email">
        </label>
        <label>
            Телефон <span aria-hidden="true">*</span>
            <input type="tel" name="phone" placeholder="+7 777 777 77 77" required autocomplete="tel">
        </label>
        <label>
            Город / примечание
            <input type="text" name="organization" placeholder="Алматы, доставка в будни" autocomplete="organization">
        </label>
        <label class="lead-form-full">
            Комментарий
            <textarea name="comment" rows="<?= $leadCompact ? 3 : 4 ?>" placeholder="Расскажите задачу"></textarea>
        </label>
    </div>

    <p class="lead-form__consent">
        Нажимая «Отправить», вы соглашаетесь с
        <a href="/privacy">политикой конфиденциальности</a>.
    </p>

    <div class="lead-form__message" role="status" aria-live="polite"></div>

    <button class="btn btn--primary" type="submit">Отправить</button>
</form>
