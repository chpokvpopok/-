<?php
/**
 * Database.php - Singleton-обёртка над PDO
 *
 * Принципы безопасности:
 *  - Учётные данные хранятся в конфиге, а не в коде
 *  - PDO::ERRMODE_EXCEPTION - все ошибки превращаются в исключения
 *  - PDO::ATTR_EMULATE_PREPARES = false - нативные prepared statements
 *    (исключает возможность SQL-инъекции через эмуляцию на уровне PDO)
 *  - PDO::ATTR_DEFAULT_FETCH_MODE = FETCH_ASSOC - удобный формат по умолчанию
 */

declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $instance = null;

    /**
     * Запрещаем прямое создание экземпляров - только через getInstance().
     */
    private function __construct() {}
    private function __clone() {}

    /**
     * Возвращает единственный экземпляр PDO-соединения (паттерн Singleton).
     * При первом вызове создаёт соединение, при последующих - возвращает кешированное.
     *
     * @throws RuntimeException если соединение не удалось установить
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/config.php';
            $db     = $config['database'];

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                $db['host'],
                $db['port'],
                $db['name']
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // ОБЯЗАТЕЛЬНО false - иначе PDO эмулирует prepared statements
                // через конкатенацию строк, сводя защиту от SQLi к нулю
                PDO::ATTR_EMULATE_PREPARES   => false,
                // Устанавливаем кодировку на уровне соединения
                (defined('Pdo\Mysql::ATTR_INIT_COMMAND')
                    ? \Pdo\Mysql::ATTR_INIT_COMMAND
                    : PDO::MYSQL_ATTR_INIT_COMMAND) => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ];

            try {
                self::$instance = new PDO($dsn, $db['user'], $db['password'], $options);
            } catch (PDOException $e) {
                // Намеренно НЕ включаем оригинальное сообщение в публичный ответ -
                // оно может содержать учётные данные или детали схемы.
                throw new RuntimeException(
                    'Ошибка подключения к базе данных. Обратитесь к администратору.',
                    500,
                    $e
                );
            }
        }

        return self::$instance;
    }
}
