<?php
/**
 * views/product/modal_order.php
 * Подключаемый фрагмент: модальное окно оформления заказа.
 * Используется через include в card.php
 */
?>
<div class="modal-overlay"
     id="order-modal"
     role="dialog"
     aria-modal="true"
     aria-labelledby="modal-title"
     hidden>

    <div class="modal">
        <button class="modal__close" id="modal-close" aria-label="Закрыть">✕</button>
        <h2 class="modal__title" id="modal-title">Оформление заказа</h2>

        <div class="order-form" id="order-form">

            <div class="form-group">
                <label class="form-label" for="input-name">
                    Имя и фамилия <span class="required" aria-hidden="true">*</span>
                </label>
                <input type="text" class="form-control" id="input-name"
                       placeholder="Введите ваше имя" autocomplete="name"
                       maxlength="150" required aria-required="true">
                <span class="form-error" id="error-name" role="alert"></span>
            </div>

            <div class="form-group">
                <label class="form-label" for="input-phone">
                    Телефон <span class="required" aria-hidden="true">*</span>
                </label>
                <input type="tel" class="form-control" id="input-phone"
                       placeholder="+7 (700) 000-00-00" autocomplete="tel"
                       maxlength="20" required aria-required="true">
                <span class="form-error" id="error-phone" role="alert"></span>
            </div>

            <div class="form-group">
                <label class="form-label" for="input-city">
                    Город <span class="required" aria-hidden="true">*</span>
                </label>
                <input type="text" class="form-control" id="input-city"
                       placeholder="Алматы" autocomplete="address-level2"
                       maxlength="100" required aria-required="true">
                <span class="form-error" id="error-city" role="alert"></span>
            </div>

            <div class="form-group">
                <label class="form-label" for="input-address">
                    Адрес доставки <span class="required" aria-hidden="true">*</span>
                </label>
                <input type="text" class="form-control" id="input-address"
                       placeholder="ул. Абая, 10, кв. 5" autocomplete="street-address"
                       maxlength="500" required aria-required="true">
                <span class="form-error" id="error-address" role="alert"></span>
            </div>

            <div class="form-group">
                <label class="form-label" for="input-comment">Комментарий</label>
                <textarea class="form-control form-control--textarea"
                          id="input-comment" rows="3"
                          placeholder="Дополнительные пожелания..."
                          maxlength="1000"></textarea>
            </div>

            <div class="modal-summary">
                <span>Итого к оплате:</span>
                <strong id="modal-price-display">0 ₸</strong>
            </div>

            <div class="form-group form-group--checkbox">
                <label class="form-label form-label--checkbox">
                    <input type="checkbox" id="input-agree" required aria-required="true">
                    <span>Принимаю условия
                        <a href="/offer" target="_blank" rel="noopener">публичной оферты</a>
                        и соглашаюсь на
                        <a href="/privacy" target="_blank" rel="noopener">обработку персональных данных</a>
                    </span>
                </label>
                <span class="form-error" id="error-agree" role="alert"></span>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn--primary btn--submit" id="btn-submit-order">
                    Подтвердить заказ
                </button>
                <button type="button" class="btn btn--ghost" id="btn-cancel-order">
                    Отмена
                </button>
            </div>

            <div class="submit-status" id="submit-status" aria-live="polite" hidden>
                <div class="spinner" aria-hidden="true"></div>
                <span>Отправляем ваш заказ...</span>
            </div>
            <div class="submit-error" id="submit-error" role="alert" hidden></div>

        </div>
    </div>
</div>
