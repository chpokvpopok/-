<?php
/**
 * views/partials/sections/lead.php
 */
declare(strict_types=1);
?>
<section id="lead-form" class="section">
    <div class="container">
        <div class="card">
            <h2 class="section__title">Запросить коммерческое предложение</h2>
            <p class="section__text">Оставьте заявку, и мы подготовим расчёт под ваш проект.</p>
            <form class="lead-form" action="#" method="post">
                <div class="lead-form-grid">
                    <label>
                        Имя
                        <input type="text" name="name" placeholder="Иван Иванов" required>
                    </label>
                    <label>
                        Email
                        <input type="email" name="email" placeholder="example@mail.ru" required>
                    </label>
                    <label>
                        Телефон
                        <input type="tel" name="phone" placeholder="+7 777 777 77 77" required>
                    </label>
                    <label class="lead-form-full">
                        Комментарий
                        <textarea name="comment" rows="4" placeholder="Расскажите задачу"></textarea>
                    </label>
                </div>
                <button class="btn btn--primary" type="submit">Отправить</button>
            </form>
        </div>
    </div>
</section>
