<?php
/**
 * index.php - Единая точка входа (Front Controller)
 *
 * ВСЕ запросы к сайту проходят через этот файл.
 * .htaccess перенаправляет сюда любой URL, который не является
 * реальным файлом или папкой.
 *
 * Задачи этого файла:
 *  1. Настроить окружение (сессия, заголовки безопасности)
 *  2. Разобрать URL и определить нужный контроллер + метод
 *  3. Вызвать контроллер и вернуть ответ
 *
 * Маршруты:
 *   GET  /                          → Главная страница
 *   GET  /catalog                   → Хаб категорий
 *   GET  /catalog/{categoryId}      → Товары категории
 *   GET  /product/{productId}       → Карточка товара с конфигуратором
 *   GET  /api/product/{id}          → JSON: данные товара
 *   GET  /api/csrf-token            → JSON: CSRF-токен
 *   POST /api/order/create          → JSON: создание заказа
 *   POST /api/lead/create           → JSON: заявка на КП
 *   GET  /order/success/{orderId}   → Страница подтверждения заказа
 *   GET  /cart                      → Корзина
 *   GET  /privacy                   → Политика конфиденциальности
 */

declare(strict_types=1);

// ------------------------------------------------------------------
// Автозагрузка классов (PSR-4 упрощённый вариант без Composer)
// ------------------------------------------------------------------
spl_autoload_register(function (string $className): void {
    // Преобразуем namespace в путь к файлу:
    // App\Controllers\OrderController → /controllers/OrderController.php
    $prefix   = 'App\\';
    $baseDir  = __DIR__ . '/';

    if (str_starts_with($className, $prefix)) {
        $relative = substr($className, strlen($prefix));
        $parts    = explode('\\', $relative);
        // Папки в проекте в нижнем регистре: controllers/, config/
        if (isset($parts[0])) {
            $parts[0] = strtolower($parts[0]);
        }
        $file = $baseDir . implode('/', $parts) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    }
});

// ------------------------------------------------------------------
// Загрузка конфига
// ------------------------------------------------------------------
$config = require __DIR__ . '/config/config.php';

// ------------------------------------------------------------------
// Заголовки безопасности HTTP
// Устанавливаем ДО любого вывода.
// ------------------------------------------------------------------

// Запрет встраивания в iframe - защита от Clickjacking
header('X-Frame-Options: DENY');

// Запрет MIME-сниффинга браузером
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/includes/view.php';

// Принудительное использование HTTPS на 1 год (только для production)
if (!$config['app']['debug']) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

// Content Security Policy: разрешаем скрипты/стили только с нашего домена
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self'; img-src 'self' data:;");

// Запрет отправки Referer на другие домены
header('Referrer-Policy: same-origin');

// ------------------------------------------------------------------
// Инициализация сессии с безопасными параметрами
// ------------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    $sess = $config['session'];

    session_set_cookie_params([
        'lifetime' => $sess['lifetime'],
        'path'     => '/',
        'domain'   => '',
        'secure'   => $sess['secure'],
        'httponly' => $sess['httponly'],
        'samesite' => $sess['samesite'],
    ]);

    session_start();

    // Защита от Session Fixation: обновляем ID сессии при каждом запуске
    // (в production делать только при логине, здесь для простоты)
}

// ------------------------------------------------------------------
// Определение языка интерфейса
// ------------------------------------------------------------------
$allowedLocales   = $config['app']['locales'];
$locale           = $_GET['lang'] ?? $_SESSION['locale'] ?? $config['app']['default_locale'];

if (!in_array($locale, $allowedLocales, true)) {
    $locale = $config['app']['default_locale'];
}
$_SESSION['locale'] = $locale;

// ------------------------------------------------------------------
// Маршрутизация
// ------------------------------------------------------------------

// Получаем чистый URI без query string и нормализуем
$requestUri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$requestMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

// Сайт в подпапке (например /shop/ в htdocs) - убираем префикс из URI
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
if ($scriptDir !== '/' && $scriptDir !== '' && str_starts_with($requestUri, $scriptDir)) {
    $requestUri = substr($requestUri, strlen($scriptDir)) ?: '/';
}
if ($requestUri === '/index.php') {
    $requestUri = '/';
}
$requestUri = '/' . trim($requestUri, '/');
if ($requestUri !== '/') {
    $requestUri = rtrim($requestUri, '/');
}

// Разбиваем URI на сегменты: "/product/42" → ['product', '42']
$segments = array_values(array_filter(explode('/', $requestUri)));

// Первый сегмент - «раздел», второй - параметр
$section = $segments[0] ?? '';
$param   = $segments[1] ?? '';
$subParam = $segments[2] ?? '';

// ------------------------------------------------------------------
// Таблица маршрутов
// ------------------------------------------------------------------

try {

    // ---- GET / (главная страница) --------------------------------
    if ($requestUri === '/' && $requestMethod === 'GET') {
        $controller = new App\Controllers\ProductController();
        $products   = $controller->getProductsByCategory(0, $locale);
        $categories = $controller->getCategories($locale);
        render('home', [
            'products' => $products,
            'categories' => $categories,
            'pageTitle' => 'Quattro - домашняя мебель',
            'pageDescription' => 'Quattro: стильная домашняя мебель для спальни, гостиной, кухни и домашнего офиса с онлайн-конфигуратором.',
            'bodyClass' => 'page-home',
        ]);
        exit;
    }

    // ---- GET /catalog и GET /catalog/{categoryId} -----------------
    if ($section === 'catalog' && $requestMethod === 'GET') {
        $controller = new App\Controllers\ProductController();

        if ($param === '') {
            $categories = $controller->getCategories($locale);
            $products   = $controller->getProductsByCategory(0, $locale);
            render('catalog/index', [
                'categories' => $categories,
                'products'   => $products,
                'pageTitle' => 'Каталог',
                'pageDescription' => 'Категории домашней мебели Quattro: спальня, гостиная, кухня и другие комнаты.',
                'bodyClass' => 'page-catalog',
            ]);
            exit;
        }

        $categoryId = (int)$param;
        $category   = $controller->getCategoryById($categoryId, $locale);

        if ($category === null) {
            http_response_code(404);
            render('errors/404', ['pageTitle' => '404 - Страница не найдена']);
            exit;
        }

        $products = $controller->getProductsByCategory($categoryId, $locale);
        render('catalog/category', [
            'category' => $category,
            'products' => $products,
            'pageTitle' => $category['name'] . ' - каталог',
            'pageDescription' => 'Каталог товаров категории ' . $category['name'],
            'bodyClass' => 'page-catalog',
        ]);
        exit;
    }

    // ---- GET /product/{id|slug} - карточка товара ----------------
    if ($section === 'product' && $requestMethod === 'GET') {
        $controller = new App\Controllers\ProductController();
        $product    = ctype_digit((string)$param)
            ? $controller->getProductById((int)$param, $locale)
            : $controller->getProductBySlug((string)$param, $locale);

        if ($product === null) {
            http_response_code(404);
            render('errors/404', ['pageTitle' => '404 - Страница не найдена']);
            exit;
        }

        $description = mb_substr(strip_tags($product['description'] ?? ''), 0, 160);
        $allProducts = $controller->getProductsByCategory(0, $locale);
        render('product/card', [
            'product' => $product,
            'configurableProducts' => filter_configurable_products($allProducts),
            'pageTitle' => $product['name'] . ' - Quattro',
            'pageDescription' => $description,
            'bodyClass' => 'page-product',
            'extraCss' => ['/public/css/configurator.css'],
            'extraJs' => ['/public/js/configurator.js'],
        ]);
        exit;
    }

    // ---- GET /api/product/{id} - JSON для AJAX -------------------
    if ($section === 'api' && $param === 'product' && $requestMethod === 'GET') {
        $productId  = (int)$subParam;
        $controller = new App\Controllers\ProductController();
        $controller->apiGetProduct($productId);
        exit;
    }

    // ---- GET /api/csrf-token - выдача CSRF-токена ----------------
    if ($section === 'api' && $param === 'csrf-token' && $requestMethod === 'GET') {
        header('Content-Type: application/json; charset=utf-8');
        $controller = new App\Controllers\OrderController();
        $token      = $controller->generateCsrfToken();
        echo json_encode(['token' => $token], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ---- POST /api/order/create - создание заказа ----------------
    if ($section === 'api' && $param === 'order' && $subParam === 'create'
        && $requestMethod === 'POST') {
        $controller = new App\Controllers\OrderController();
        $controller->createOrder();
        exit;
    }

    // ---- POST /api/lead/create - заявка на КП --------------------
    if ($section === 'api' && $param === 'lead' && $subParam === 'create'
        && $requestMethod === 'POST') {
        $controller = new App\Controllers\LeadController();
        $controller->createLead();
        exit;
    }

    // ---- GET /order/success/{orderId} - страница «Заказ принят» --
    if ($section === 'order' && $param === 'success' && $requestMethod === 'GET') {
        $orderId    = (int)$subParam;
        $controller = new App\Controllers\OrderController();
        $controller->showSuccess($orderId);
        exit;
    }

    // ---- GET /privacy - политика конфиденциальности ------------
    if ($section === 'privacy' && $requestMethod === 'GET') {
        render('pages/privacy', [
            'pageTitle' => 'Политика конфиденциальности - Quattro',
            'pageDescription' => 'Как Quattro обрабатывает персональные данные в формах заявки и заказа.',
            'bodyClass' => 'page-privacy',
        ]);
        exit;
    }

    // ---- GET /cart - корзина ------------------------------------
    if ($section === 'cart' && $requestMethod === 'GET') {
        render('cart', [
            'pageTitle' => 'Корзина - Quattro',
            'pageDescription' => 'Оформление заказа через конфигуратор Quattro.',
            'bodyClass' => 'page-cart',
        ]);
        exit;
    }

    // ---- 404 - ничего не совпало --------------------------------
    http_response_code(404);
    render('errors/404', ['pageTitle' => '404 - Страница не найдена']);

} catch (Throwable $e) {
    // Глобальный обработчик непойманных исключений
    // В debug-режиме показываем детали, в production - только общее сообщение
    if ($config['app']['debug']) {
        http_response_code(500);
        echo '<pre style="background:#1a1d27;color:#ef4444;padding:20px;font-family:monospace;">';
        echo '<strong>Ошибка:</strong> ' . htmlspecialchars($e->getMessage()) . "\n\n";
        echo '<strong>Файл:</strong> '   . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . "\n\n";
        echo '<strong>Трассировка:</strong>' . "\n" . htmlspecialchars($e->getTraceAsString());
        echo '</pre>';
    } else {
        // Логируем в файл (не показываем детали пользователю)
        error_log('[' . date('Y-m-d H:i:s') . '] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        http_response_code(500);
        render('errors/500', ['pageTitle' => '500 - Ошибка сервера']);
    }
}
