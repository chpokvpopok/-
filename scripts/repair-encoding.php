<?php
/**
 * Восстановление кириллицы в БД (Windows: после импорта с «???????»).
 *
 * Запуск из корня проекта:
 *   php scripts/repair-encoding.php
 *
 * Повторно применяет миграции 010 (названия) и 006 (опции конфигуратора)
 * через копию SQL в каталог без кириллицы в пути + utf8mb4.
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$config = require $root . '/config/config.php';
$db = $config['database'];

$stagingDir = PHP_OS_FAMILY === 'Windows'
    ? 'C:/ProgramData/furniture_platform_sql'
    : '/tmp/furniture_platform_sql';

if (!is_dir($stagingDir) && !mkdir($stagingDir, 0775, true) && !is_dir($stagingDir)) {
    fwrite(STDERR, "Не удалось создать каталог: {$stagingDir}\n");
    exit(1);
}

$migrations = [
    '010_repair_utf8_texts.sql',
    '006_product_specific_config.sql',
];

$host = $db['host'];
$port = (int)$db['port'];
$user = $db['user'];
$pass = $db['password'];
$name = $db['name'];

$mysqlBin = getenv('MYSQL') ?: 'mysql';
if (PHP_OS_FAMILY === 'Windows') {
    $candidates = [
        'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysql.exe',
        'C:\\Program Files\\MySQL\\MySQL Server 8.4\\bin\\mysql.exe',
    ];
    foreach ($candidates as $candidate) {
        if (is_file($candidate)) {
            $mysqlBin = $candidate;
            break;
        }
    }
}

$args = [
    $mysqlBin,
    '-h', $host,
    '-P', (string)$port,
    '-u', $user,
    '--default-character-set=utf8mb4',
    $name,
];

if ($pass !== '') {
    $args[] = '-p' . $pass;
}

foreach ($migrations as $file) {
    $source = $root . '/sql/migrations/' . $file;
    if (!is_file($source)) {
        fwrite(STDERR, "Нет файла: {$source}\n");
        exit(1);
    }

    $sql = file_get_contents($source);
    if ($sql === false) {
        fwrite(STDERR, "Не удалось прочитать: {$source}\n");
        exit(1);
    }
    if (str_starts_with($sql, "\xEF\xBB\xBF")) {
        $sql = substr($sql, 3);
    }

    $stagingFile = $stagingDir . '/' . $file;
    file_put_contents($stagingFile, $sql);

    $sourcePath = str_replace('\\', '/', $stagingFile);
    $cmd = $args;
    $cmd[] = '-e';
    $cmd[] = 'source ' . $sourcePath;

    echo "Применяю: {$file}\n";

    $descriptor = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
    $proc = proc_open($cmd, $descriptor, $pipes);
    if (!is_resource($proc)) {
        fwrite(STDERR, "Не удалось запустить mysql\n");
        exit(1);
    }
    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $code = proc_close($proc);

    if ($code !== 0) {
        fwrite(STDERR, "Ошибка mysql (код {$code}) для {$file}\n");
        if ($stderr !== '') {
            fwrite(STDERR, $stderr . "\n");
        }
        if ($stdout !== '') {
            fwrite(STDERR, $stdout . "\n");
        }
        exit(1);
    }
}

echo "Готово. Проверка name_ru:\n";
require_once $root . '/config/Database.php';
$pdo = App\Config\Database::getInstance();
$rows = $pdo->query("SELECT sku, name_ru FROM products WHERE sku = 'HOME-OFFICE-01' LIMIT 1")->fetchAll();
foreach ($rows as $row) {
    echo $row['sku'] . ' => ' . $row['name_ru'] . "\n";
}
