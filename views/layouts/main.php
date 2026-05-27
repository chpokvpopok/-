<?php
/**
 * views/layouts/main.php
 * Общий layout для сайта.
 * Ожидает: $pageTitle, $pageDescription, $bodyClass, $extraCss, $extraJs, $content
 */

declare(strict_types=1);

$locale = $_SESSION['locale'] ?? 'ru';
$bodyClass = $bodyClass ?? '';
$extraCss = is_array($extraCss) ? $extraCss : [$extraCss];
$extraJs = is_array($extraJs) ? $extraJs : [$extraJs];
?>
<!DOCTYPE html>
<html lang="<?= e($locale) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <link rel="stylesheet" href="/public/css/site.css">
    <?php foreach ($extraCss as $css): ?>
        <?php if (trim((string)$css) !== ''): ?>
            <link rel="stylesheet" href="<?= e((string)$css) ?>">
        <?php endif ?>
    <?php endforeach ?>
</head>
<body class="<?= e($bodyClass) ?>">
    <?php require __DIR__ . '/../partials/header.php'; ?>

    <?= $content ?>

    <?php require __DIR__ . '/../partials/footer.php'; ?>

    <script src="/public/js/site.js" defer></script>
    <?php foreach ($extraJs as $js): ?>
        <?php if (trim((string)$js) !== ''): ?>
            <script src="<?= e((string)$js) ?>" defer></script>
        <?php endif ?>
    <?php endforeach ?>
</body>
</html>
