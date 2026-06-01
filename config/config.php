<?php
/**
 * config.php - Централизованная конфигурация платформы
 *
 * ВАЖНО: Этот файл должен быть исключён из системы контроля версий (.gitignore).
 * В production-среде рекомендуется читать значения из переменных окружения ($_ENV).
 */

declare(strict_types=1);

return [

    // ------------------------------------------------------------------
    // Настройки подключения к базе данных
    // ------------------------------------------------------------------
    'database' => [
        'host'     => getenv('DB_HOST')     ?: 'localhost',
        'port'     => (int)(getenv('DB_PORT') ?: 3306),
        'name'     => getenv('DB_NAME')     ?: 'furniture_platform',
        // XAMPP / локально: root без пароля. Docker/production: DB_USER / DB_PASSWORD.
        'user'     => getenv('DB_USER') !== false && getenv('DB_USER') !== '' ? getenv('DB_USER') : 'root',
        'password' => getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '',
    ],

    // ------------------------------------------------------------------
    // Настройки сессий
    // ------------------------------------------------------------------
    'session' => [
        // Время жизни сессионного cookie (секунды): 0 = до закрытия браузера
        'lifetime'  => 0,
        // Передавать cookie только по HTTPS (для localhost: SESSION_SECURE=false)
        // На http://localhost cookie с Secure не сохранится - для XAMPP по умолчанию false.
        'secure'    => filter_var(getenv('SESSION_SECURE') ?: 'false', FILTER_VALIDATE_BOOLEAN),
        // Запретить доступ к cookie из JavaScript (защита от XSS-кражи сессии)
        'httponly'  => true,
        // Строгая политика SameSite - защита от CSRF
        'samesite'  => 'Strict',
    ],

    // ------------------------------------------------------------------
    // Настройки приложения
    // ------------------------------------------------------------------
    'app' => [
        'name'     => 'Furniture Platform',
        'base_url' => getenv('APP_URL') ?: 'https://your-domain.kz',
        'debug'    => filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN),
        // Поддерживаемые языки интерфейса
        'locales'  => ['ru', 'kk'],
        'default_locale' => 'ru',
    ],

    // ------------------------------------------------------------------
    // Настройки безопасности
    // ------------------------------------------------------------------
    'security' => [
        // bcrypt cost factor - минимально допустимое значение по требованиям ИБ
        'bcrypt_cost'        => 12,
        // Максимальное число неудачных попыток входа до временной блокировки
        'login_attempts_max' => 5,
        // Время блокировки аккаунта после превышения попыток (секунды)
        'lockout_duration'   => 900,
    ],
];
