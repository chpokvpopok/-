<?php
/**
 * OrderController.php
 *
 * Отвечает за:
 *  - Приём JSON-запроса на создание заказа от frontend-конфигуратора
 *  - Валидацию входящих данных (типы, диапазоны, наличие обязательных полей)
 *  - Расчёт итоговой стоимости с учётом выбранных опций (price_modifier)
 *  - Защиту от CSRF через сессионный синхронизирующий токен
 *  - XSS-экранирование пользовательского ввода перед записью в БД
 *  - Запись заказа в таблицы orders и order_items в одной транзакции
 *
 * Безопасность:
 *  - PDO prepared statements → защита от SQL-инъекций
 *  - CSRF-токен в сессии → защита от межсайтовой подделки запросов
 *  - htmlspecialchars() на все строки от пользователя → защита от XSS
 *  - Валидация типов: intval/floatval/filter_var → защита от type juggling
 *  - Транзакция InnoDB → гарантия ACID при ошибке на полпути
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use PDO;
use PDOException;
use InvalidArgumentException;
use RuntimeException;

class OrderController
{
    private PDO $db;

    // Максимально допустимое количество позиций в одном заказе
    private const MAX_ITEMS = 50;

    // Допустимое количество единиц одной позиции
    private const MAX_QTY   = 100;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->startSecureSession();
    }

    // ==================================================================
    // CSRF-ЗАЩИТА
    // ==================================================================

    /**
     * Инициализирует PHP-сессию с безопасными параметрами cookie.
     * Должна вызываться до любой работы с сессией.
     */
    private function startSecureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $config = require __DIR__ . '/../config/config.php';
            $sess   = $config['session'];

            session_set_cookie_params([
                'lifetime' => $sess['lifetime'],
                'path'     => '/',
                'domain'   => '',
                'secure'   => $sess['secure'],    // только HTTPS
                'httponly' => $sess['httponly'],   // недоступно из JS
                'samesite' => $sess['samesite'],   // защита от CSRF
            ]);

            session_start();
        }
    }

    /**
     * Генерирует криптографически стойкий CSRF-токен и сохраняет его
     * в сессии. Возвращает токен для встраивания в HTML-форму.
     *
     * @return string  32-байтный hex-токен
     */
    public function generateCsrfToken(): string
    {
        // random_bytes() — CSPRNG, безопаснее mt_rand() / uniqid()
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    /**
     * Проверяет CSRF-токен из запроса против значения в сессии.
     * Использует hash_equals() для защиты от timing-атак.
     *
     * @param string $tokenFromRequest  Токен, переданный клиентом
     * @throws RuntimeException         Если токен невалиден или отсутствует
     */
    private function validateCsrfToken(string $tokenFromRequest): void
    {
        $sessionToken = $_SESSION['csrf_token'] ?? '';

        if (
            empty($sessionToken) ||
            !hash_equals($sessionToken, $tokenFromRequest)
        ) {
            throw new RuntimeException('Недействительный CSRF-токен.', 403);
        }

        // Токен одноразовый: инвалидируем после успешной проверки,
        // чтобы повторная отправка той же формы не прошла проверку.
        unset($_SESSION['csrf_token']);
    }

    // ==================================================================
    // ТОЧКИ ВХОДА (вызываются из маршрутизатора)
    // ==================================================================

    /**
     * POST /api/order/create
     *
     * Принимает тело запроса в формате JSON:
     * {
     *   "csrf_token": "abc123...",
     *   "items": [
     *     {
     *       "product_id": 1,
     *       "quantity": 1,
     *       "selected_options": [3, 7, 9]   // массив ID выбранных опций
     *     }
     *   ],
     *   "delivery": {
     *     "name":    "Иван Иванов",
     *     "phone":   "+77001234567",
     *     "city":    "Алматы",
     *     "address": "ул. Абая, 10, кв. 5",
     *     "comment": ""
     *   }
     * }
     *
     * Возвращает JSON:
     *   { "success": true, "order_id": 42, "total_price": 985000 }
     *   или
     *   { "success": false, "error": "Текст ошибки" }
     */
    public function createOrder(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Разрешаем только POST-запросы
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Метод не разрешён.'],
                             JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            // --- 1. Чтение и первичный разбор JSON-тела запроса ---
            $rawBody = file_get_contents('php://input');
            $data    = json_decode($rawBody, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                throw new InvalidArgumentException('Некорректный формат JSON в теле запроса.');
            }

            // --- 2. Проверка CSRF-токена ---
            $csrfToken = $this->sanitizeString($data['csrf_token'] ?? '');
            $this->validateCsrfToken($csrfToken);

            // --- 3. Валидация и санитизация входных данных ---
            $items    = $this->validateItems($data['items'] ?? []);
            $delivery = $this->validateDelivery($data['delivery'] ?? []);

            // --- 4. Расчёт стоимости и запись в БД ---
            $orderId = $this->persistOrder($items, $delivery);

            http_response_code(201);
            echo json_encode([
                'success'  => true,
                'order_id' => $orderId,
                'message'  => 'Ваш заказ успешно принят. Менеджер свяжется с вами в течение 30 минут.',
            ], JSON_UNESCAPED_UNICODE);

        } catch (InvalidArgumentException $e) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => $e->getMessage()],
                             JSON_UNESCAPED_UNICODE);

        } catch (RuntimeException $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()],
                             JSON_UNESCAPED_UNICODE);

        } catch (\Throwable $e) {
            // Непредвиденная ошибка: логируем, но не раскрываем детали клиенту
            error_log('[OrderController] Unexpected error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Внутренняя ошибка сервера.'],
                             JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * GET /order/success/{id}
     * Отображает страницу подтверждения заказа.
     */
    public function showSuccess(int $orderId): void
    {
        $order = $this->getOrderById($orderId);

        if ($order === null) {
            http_response_code(404);
            include __DIR__ . '/../views/errors/404.php';
            return;
        }

        include __DIR__ . '/../views/order/success.php';
    }

    // ==================================================================
    // ВАЛИДАЦИЯ
    // ==================================================================

    /**
     * Валидирует массив позиций заказа.
     * Каждая позиция: product_id (int > 0), quantity (int 1..MAX_QTY),
     *                 selected_options (array of int)
     *
     * @param mixed $rawItems
     * @return array  Валидированный массив позиций
     * @throws InvalidArgumentException
     */
    private function validateItems(mixed $rawItems): array
    {
        if (!is_array($rawItems) || empty($rawItems)) {
            throw new InvalidArgumentException('Корзина не может быть пустой.');
        }

        if (count($rawItems) > self::MAX_ITEMS) {
            throw new InvalidArgumentException(
                sprintf('В заказе не может быть более %d позиций.', self::MAX_ITEMS)
            );
        }

        $validated = [];

        foreach ($rawItems as $index => $item) {
            if (!is_array($item)) {
                throw new InvalidArgumentException(
                    "Позиция #{$index}: некорректный формат данных."
                );
            }

            // product_id — целое положительное число
            $productId = filter_var($item['product_id'] ?? 0,
                                    FILTER_VALIDATE_INT,
                                    ['options' => ['min_range' => 1]]);
            if ($productId === false) {
                throw new InvalidArgumentException(
                    "Позиция #{$index}: некорректный идентификатор товара."
                );
            }

            // quantity — целое от 1 до MAX_QTY
            $quantity = filter_var($item['quantity'] ?? 1,
                                   FILTER_VALIDATE_INT,
                                   ['options' => ['min_range' => 1, 'max_range' => self::MAX_QTY]]);
            if ($quantity === false) {
                throw new InvalidArgumentException(
                    "Позиция #{$index}: некорректное количество."
                );
            }

            // selected_options — массив целых чисел (может быть пустым)
            $rawOptions = $item['selected_options'] ?? [];
            if (!is_array($rawOptions)) {
                throw new InvalidArgumentException(
                    "Позиция #{$index}: опции должны быть переданы массивом."
                );
            }

            $optionIds = [];
            foreach ($rawOptions as $optId) {
                $validated_opt = filter_var($optId,
                                            FILTER_VALIDATE_INT,
                                            ['options' => ['min_range' => 1]]);
                if ($validated_opt === false) {
                    throw new InvalidArgumentException(
                        "Позиция #{$index}: некорректный ID опции."
                    );
                }
                $optionIds[] = (int)$validated_opt;
            }

            $validated[] = [
                'product_id'       => (int)$productId,
                'quantity'         => (int)$quantity,
                'selected_options' => $optionIds,
            ];
        }

        return $validated;
    }

    /**
     * Валидирует и санитизирует данные доставки.
     *
     * @param mixed $rawDelivery
     * @return array
     * @throws InvalidArgumentException
     */
    private function validateDelivery(mixed $rawDelivery): array
    {
        if (!is_array($rawDelivery)) {
            throw new InvalidArgumentException('Отсутствуют данные доставки.');
        }

        $name    = $this->sanitizeString($rawDelivery['name']    ?? '');
        $phone   = $this->sanitizeString($rawDelivery['phone']   ?? '');
        $city    = $this->sanitizeString($rawDelivery['city']    ?? '');
        $address = $this->sanitizeString($rawDelivery['address'] ?? '');
        $comment = $this->sanitizeString($rawDelivery['comment'] ?? '');

        // Обязательные поля
        if (mb_strlen($name) < 2 || mb_strlen($name) > 150) {
            throw new InvalidArgumentException('Укажите корректное имя (2–150 символов).');
        }

        // Валидация телефона: допускаем форматы +7XXXXXXXXXX или 8XXXXXXXXXX
        if (!preg_match('/^(\+7|8)\d{10}$/', preg_replace('/[\s\-()]/', '', $phone))) {
            throw new InvalidArgumentException('Укажите корректный номер телефона.');
        }

        if (mb_strlen($city) < 2 || mb_strlen($city) > 100) {
            throw new InvalidArgumentException('Укажите корректное название города.');
        }

        if (mb_strlen($address) < 5 || mb_strlen($address) > 500) {
            throw new InvalidArgumentException('Укажите корректный адрес доставки.');
        }

        return [
            'name'    => $name,
            'phone'   => $phone,
            'city'    => $city,
            'address' => $address,
            'comment' => mb_substr($comment, 0, 1000), // ограничиваем длину комментария
        ];
    }

    // ==================================================================
    // БИЗНЕС-ЛОГИКА: РАСЧЁТ СТОИМОСТИ И ЗАПИСЬ В БД
    // ==================================================================

    /**
     * Рассчитывает стоимость позиций заказа и записывает заказ в БД
     * в рамках одной транзакции InnoDB (гарантия ACID).
     *
     * Алгоритм расчёта стоимости:
     *   item_price = product.base_price + SUM(selected option.price_modifier)
     *   total      = SUM(item_price * quantity) по всем позициям
     *
     * @param array $items     Валидированные позиции заказа
     * @param array $delivery  Валидированные данные доставки
     * @return int             ID созданного заказа
     * @throws RuntimeException|InvalidArgumentException
     */
    private function persistOrder(array $items, array $delivery): int
    {
        $this->db->beginTransaction();

        try {
            $totalOrderPrice = 0.0;
            $processedItems  = [];

            foreach ($items as $item) {
                $productId = $item['product_id'];
                $quantity  = $item['quantity'];
                $optionIds = $item['selected_options'];

                // --- Получаем базовую цену товара из БД ---
                // Это критически важно: нельзя доверять цене, переданной клиентом —
                // клиентская часть хранит отображаемые цены, реальные берём из БД.
                $productStmt = $this->db->prepare(
                    'SELECT id, base_price FROM products WHERE id = :id AND active = 1 LIMIT 1'
                );
                $productStmt->execute([':id' => $productId]);
                $product = $productStmt->fetch();

                if ($product === false) {
                    throw new InvalidArgumentException(
                        "Товар с ID {$productId} не найден или недоступен."
                    );
                }

                $itemPrice      = (float)$product['base_price'];
                $selectedOptions = [];

                // --- Получаем и суммируем price_modifier выбранных опций ---
                if (!empty($optionIds)) {
                    $placeholders = implode(',', array_fill(0, count($optionIds), '?'));

                    $optStmt = $this->db->prepare("
                        SELECT id, option_name_ru, price_modifier
                        FROM product_options
                        WHERE id IN ({$placeholders})
                          AND product_id = ?
                    ");

                    // Передаём: сначала ID опций, затем ID продукта
                    $optStmt->execute([...$optionIds, $productId]);
                    $fetchedOptions = $optStmt->fetchAll();

                    // Защита: количество найденных опций должно совпадать с запрошенными
                    if (count($fetchedOptions) !== count($optionIds)) {
                        throw new InvalidArgumentException(
                            "Одна или несколько выбранных опций для товара ID {$productId} не существуют."
                        );
                    }

                    foreach ($fetchedOptions as $opt) {
                        $itemPrice += (float)$opt['price_modifier'];
                        $selectedOptions[] = [
                            'option_id'      => (int)$opt['id'],
                            'option_name_ru' => $opt['option_name_ru'],
                            'price_modifier' => (float)$opt['price_modifier'],
                        ];
                    }
                }

                $lineTotal        = $itemPrice * $quantity;
                $totalOrderPrice += $lineTotal;

                $processedItems[] = [
                    'product_id'           => $productId,
                    'quantity'             => $quantity,
                    'price'                => $itemPrice,
                    'selected_options_json' => json_encode(
                        $selectedOptions,
                        JSON_UNESCAPED_UNICODE
                    ),
                ];
            }

            // --- Записываем заголовок заказа в таблицу orders ---
            $deliveryJson = json_encode($delivery, JSON_UNESCAPED_UNICODE);

            $userId = $_SESSION['user_id'] ?? null; // null если гостевой заказ

            $orderStmt = $this->db->prepare("
                INSERT INTO orders (user_id, total_price, status, delivery_info, created_at)
                VALUES (:user_id, :total_price, 'new', :delivery_info, NOW())
            ");
            $orderStmt->execute([
                ':user_id'       => $userId,
                ':total_price'   => round($totalOrderPrice, 2),
                ':delivery_info' => $deliveryJson,
            ]);

            $orderId = (int)$this->db->lastInsertId();

            // --- Записываем позиции заказа в таблицу order_items ---
            $itemStmt = $this->db->prepare("
                INSERT INTO order_items
                    (order_id, product_id, selected_options_json, quantity, price)
                VALUES
                    (:order_id, :product_id, :options_json, :quantity, :price)
            ");

            foreach ($processedItems as $processed) {
                $itemStmt->execute([
                    ':order_id'    => $orderId,
                    ':product_id'  => $processed['product_id'],
                    ':options_json' => $processed['selected_options_json'],
                    ':quantity'    => $processed['quantity'],
                    ':price'       => round($processed['price'], 2),
                ]);
            }

            // Всё прошло успешно — фиксируем транзакцию
            $this->db->commit();

            return $orderId;

        } catch (\Throwable $e) {
            // При любой ошибке откатываем транзакцию — частичных заказов не будет
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Возвращает данные заказа по его ID (для страницы подтверждения).
     */
    private function getOrderById(int $orderId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, total_price, status, delivery_info, created_at
             FROM orders WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $orderId]);
        $order = $stmt->fetch();

        return $order !== false ? $order : null;
    }

    // ==================================================================
    // УТИЛИТЫ САНИТИЗАЦИИ
    // ==================================================================

    /**
     * Очищает строку от пробелов и экранирует HTML-спецсимволы.
     * htmlspecialchars с ENT_QUOTES экранирует как " так и ' —
     * защита от XSS при последующем выводе в HTML-контексте.
     *
     * @param mixed  $value   Входное значение
     * @param int    $maxLen  Максимальная длина после обрезки
     * @return string
     */
    private function sanitizeString(mixed $value, int $maxLen = 500): string
    {
        if (!is_string($value) && !is_numeric($value)) {
            return '';
        }

        $clean = trim((string)$value);
        $clean = mb_substr($clean, 0, $maxLen);

        // ENT_QUOTES: экранируем и двойные, и одинарные кавычки
        return htmlspecialchars($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
