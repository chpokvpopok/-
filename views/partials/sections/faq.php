<?php
/**
 * views/partials/sections/faq.php
 */
declare(strict_types=1);
$faqs = [
    [
        'question' => 'Сколько времени занимает производство?',
        'answer' => 'Стандартный цикл от заявки до готового модуля — 3–5 недель в зависимости от комплектации.',
    ],
    [
        'question' => 'Можно ли заказать нестандартные размеры?',
        'answer' => 'Да, мы проектируем мебель по вашим техническим требованиям и рабочей зоне.',
    ],
    [
        'question' => 'Как рассчитывается цена?',
        'answer' => 'Цена формируется из базовой модели и выбранных опций: материал, дополнительные панели, системы подсветки.',
    ],
    [
        'question' => 'Есть ли гарантия на изделия?',
        'answer' => 'Мы даём гарантию на конструкцию и фурнитуру, а также поддержку после установки.',
    ],
];
?>
<section class="section section--sm">
    <div class="container">
        <h2 class="section__title">Часто задаваемые вопросы</h2>
        <div class="faq-list">
            <?php foreach ($faqs as $index => $item): ?>
                <div class="faq-item">
                    <button type="button" class="faq-question" aria-expanded="false" aria-controls="faq-answer-<?= $index ?>">
                        <?= e($item['question']) ?>
                        <span class="faq-toggle">+</span>
                    </button>
                    <div id="faq-answer-<?= $index ?>" class="faq-answer" hidden>
                        <p><?= e($item['answer']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
