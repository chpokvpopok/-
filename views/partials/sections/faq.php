<?php
declare(strict_types=1);

$faqs = [
    [
        'question' => 'Сколько времени занимает изготовление?',
        'answer' => 'Стандартный срок изготовления - 4-6 недель в зависимости от модели, материалов и загрузки производства.',
    ],
    [
        'question' => 'Можно ли заказать нестандартные размеры?',
        'answer' => 'Да, мы подбираем габариты и комплектацию под вашу планировку и пожелания по отделке.',
    ],
    [
        'question' => 'Как рассчитывается цена в конфигураторе?',
        'answer' => 'Итог складывается из базовой модели и выбранных опций: материал, фурнитура, дополнительные модули.',
    ],
    [
        'question' => 'Есть ли гарантия на мебель?',
        'answer' => 'Да, предоставляем гарантию на конструкцию и фурнитуру, а также консультацию после доставки.',
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
