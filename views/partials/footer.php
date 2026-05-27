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
                <li><a href="/product/1">Конфигуратор</a></li>
            </ul>
        </div>

        <div>
            <p class="footer-title">Компания</p>
            <ul class="footer-list">
                <li><a href="/#cases">Кейсы</a></li>
                <li><a href="#">Политика</a></li>
            </ul>
        </div>

        <div>
            <p class="footer-title">Контакты</p>
            <p class="text-muted">
                <a href="mailto:<?= e($contactEmail) ?>"><?= e($contactEmail) ?></a><br>
                <a href="<?= e($contactPhoneHref) ?>"><?= e($contactPhone) ?></a>
            </p>
        </div>

        <div>
            <p class="footer-title">Адрес</p>
            <p class="text-muted"><?= e($contactAddress) ?></p>
        </div>

        <div class="footer-copy">© 2026 Quattro. Все права защищены.</div>
    </div>
</footer>
