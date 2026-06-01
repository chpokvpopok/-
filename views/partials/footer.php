<?php
/**
 * views/partials/footer.php
 */
declare(strict_types=1);

require __DIR__ . '/contacts.php';
?>
<footer class="site-footer">
    <div class="container">
        <div>
            <p class="footer-title">Продукция</p>
            <ul class="footer-list">
                <li><a href="/catalog">Каталог</a></li>
                <li><a href="/#configurator-models">Конфигуратор</a></li>
            </ul>
        </div>

        <div>
            <p class="footer-title">Компания</p>
            <ul class="footer-list">
                <li><a href="/#cases">Кейсы</a></li>
                <li><a href="/privacy">Политика конфиденциальности</a></li>
            </ul>
        </div>

        <div>
            <p class="footer-title">Контакты</p>
            <p class="text-muted">
                <a href="mailto:<?= e($contactEmail) ?>"><?= e($contactEmail) ?></a><br>
                <a href="<?= e($contactPhoneHref) ?>"><?= e($contactPhone) ?></a><br>
                <a href="<?= e($contactInstagramHref) ?>" target="_blank" rel="noopener noreferrer">@<?= e($contactInstagram) ?></a>
            </p>
        </div>

        <div>
            <p class="footer-title">Адрес</p>
            <p class="text-muted"><?= e($contactAddress) ?></p>
        </div>

        <div class="footer-copy">© 2026 Quattro. Все права защищены.</div>
    </div>
</footer>
