<?php
/**
 * ProductController.php
 *
 * Отвечает за:
 *  - Получение списка товаров с их конфигурационными опциями
 *  - Получение детальной информации об одном товаре
 *
 * Паттерн MVC: Controller вызывает методы Model и передаёт
 * результат в View. Бизнес-логика выборки инкапсулирована здесь;
 * SQL-запросы изолированы в методах данного класса.
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use PDO;

class ProductController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ------------------------------------------------------------------
    // Публичные методы (точки входа из маршрутизатора)
    // ------------------------------------------------------------------

    /**
     * Возвращает список категорий для хаба каталога.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getCategories(string $locale = 'ru'): array
    {
        $locale  = in_array($locale, ['ru', 'kk'], true) ? $locale : 'ru';
        $nameCol = "name_{$locale}";

        $sql = "
            SELECT
                c.id,
                c.{$nameCol} AS name,
                c.slug,
                (
                    SELECT COUNT(*)
                    FROM products p
                    WHERE p.category_id = c.id
                      AND p.active = 1
                ) AS product_count
            FROM categories c
            ORDER BY c.id ASC
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    /**
     * Возвращает одну категорию по ID или null, если не найдена.
     *
     * @return array<string, mixed>|null
     */
    public function getCategoryById(int $categoryId, string $locale = 'ru'): ?array
    {
        if ($categoryId <= 0) {
            return null;
        }

        $locale  = in_array($locale, ['ru', 'kk'], true) ? $locale : 'ru';
        $nameCol = "name_{$locale}";

        $sql = "
            SELECT
                c.id,
                c.{$nameCol} AS name,
                c.slug
            FROM categories c
            WHERE c.id = :category_id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':category_id' => $categoryId]);
        $category = $stmt->fetch();

        return $category === false ? null : $category;
    }

    /**
     * Возвращает список активных товаров заданной категории
     * вместе с их конфигурационными опциями.
     *
     * Метод реализует «жадную загрузку» (eager loading) опций:
     * сначала загружаются товары, затем одним запросом - все их опции,
     * после чего происходит сборка результата в PHP.
     * Это позволяет избежать проблемы N+1 запросов.
     *
     * @param int $categoryId  ID категории (0 = все категории)
     * @param string $locale   Языковой код ('ru' или 'kk')
     * @return array           Массив товаров с вложенными опциями
     */
    public function getProductsByCategory(int $categoryId = 0, string $locale = 'ru'): array
    {
        // Валидация локали - только допустимые значения
        $locale = in_array($locale, ['ru', 'kk'], true) ? $locale : 'ru';

        // Динамически подставляем суффикс языка в имена столбцов.
        // Имена столбцов нельзя параметризовать через PDO (только значения),
        // поэтому используем whitelist-подход: допустимое значение уже проверено выше.
        $nameCol = "p.name_{$locale}";
        $descCol = "p.description_{$locale}";

        // --- Запрос 1: Выборка товаров ---
        $sql = "
            SELECT
                p.id,
                p.category_id,
                {$nameCol}        AS name,
                {$descCol}        AS description,
                p.base_price,
                p.image_preview,
                p.sku,
                p.slug,
                c.name_{$locale}  AS category_name
            FROM products p
            INNER JOIN categories c ON c.id = p.category_id
            WHERE p.active = 1
        ";

        $params = [];

        if ($categoryId > 0) {
            $sql      .= ' AND p.category_id = :category_id';
            $params[':category_id'] = $categoryId;
        }

        $sql .= ' ORDER BY p.id ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();

        if (empty($products)) {
            return [];
        }

        // --- Запрос 2: Все опции для найденных товаров одним запросом ---
        // Собираем массив ID товаров для IN-клаузы
        $productIds    = array_column($products, 'id');
        $placeholders  = implode(',', array_fill(0, count($productIds), '?'));

        $optSql = "
            SELECT
                po.id,
                po.product_id,
                po.option_name_{$locale}  AS option_name,
                po.option_type,
                po.option_value_{$locale} AS option_value,
                po.option_group,
                po.price_modifier,
                po.sort_order
            FROM product_options po
            WHERE po.product_id IN ({$placeholders})
            ORDER BY po.product_id ASC, po.sort_order ASC
        ";

        $optStmt = $this->db->prepare($optSql);
        // Передаём массив ID как последовательные позиционные параметры
        $optStmt->execute($productIds);
        $allOptions = $optStmt->fetchAll();

        // --- Сборка: группируем опции по product_id ---
        $optionsByProduct = [];
        foreach ($allOptions as $option) {
            $pid = (int)$option['product_id'];
            $optionsByProduct[$pid][] = $option;
        }

        // Присоединяем опции к товарам
        foreach ($products as &$product) {
            $pid             = (int)$product['id'];
            $product['options'] = $optionsByProduct[$pid] ?? [];
            // Приводим base_price к float для корректной JSON-сериализации
            $product['base_price'] = (float)$product['base_price'];
        }
        unset($product);

        return $products;
    }

    /**
     * Возвращает детальные данные одного товара по его ID,
     * включая все конфигурационные опции.
     *
     * @param int    $productId  ID товара
     * @param string $locale     Языковой код
     * @return array|null        Данные товара или null если не найден/скрыт
     */
    public function getProductById(int $productId, string $locale = 'ru'): ?array
    {
        $locale  = in_array($locale, ['ru', 'kk'], true) ? $locale : 'ru';

        $nameCol = "p.name_{$locale}";
        $descCol = "p.description_{$locale}";

        $sql = "
            SELECT
                p.id,
                {$nameCol}        AS name,
                {$descCol}        AS description,
                p.base_price,
                p.image_preview,
                p.sku,
                p.slug,
                p.category_id,
                c.name_{$locale}  AS category_name,
                c.slug            AS category_slug
            FROM products p
            INNER JOIN categories c ON c.id = p.category_id
            WHERE p.id = :product_id
              AND p.active = 1
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':product_id' => $productId]);
        $product = $stmt->fetch();

        if ($product === false) {
            return null;
        }

        return $this->attachProductOptions($product, $locale);
    }

    /**
     * Товар по URL-slug (для ссылок вида /product/bedroom-set-1).
     *
     * @return array<string, mixed>|null
     */
    public function getProductBySlug(string $slug, string $locale = 'ru'): ?array
    {
        $slug = trim($slug);

        if ($slug === '') {
            return null;
        }

        $locale  = in_array($locale, ['ru', 'kk'], true) ? $locale : 'ru';
        $nameCol = "p.name_{$locale}";
        $descCol = "p.description_{$locale}";

        $sql = "
            SELECT
                p.id,
                {$nameCol}        AS name,
                {$descCol}        AS description,
                p.base_price,
                p.image_preview,
                p.sku,
                p.slug,
                p.category_id,
                c.name_{$locale}  AS category_name,
                c.slug            AS category_slug
            FROM products p
            INNER JOIN categories c ON c.id = p.category_id
            WHERE p.slug = :slug
              AND p.active = 1
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':slug' => $slug]);
        $product = $stmt->fetch();

        if ($product === false) {
            return null;
        }

        return $this->attachProductOptions($product, $locale);
    }

    /**
     * @param array<string, mixed> $product
     * @return array<string, mixed>
     */
    private function attachProductOptions(array $product, string $locale): array
    {
        $productId = (int)$product['id'];

        // Загружаем опции этого товара
        $optSql = "
            SELECT
                id,
                option_name_{$locale}   AS option_name,
                option_type,
                option_value_{$locale}  AS option_value,
                option_group,
                price_modifier,
                sort_order
            FROM product_options
            WHERE product_id = :product_id
            ORDER BY sort_order ASC
        ";

        $optStmt = $this->db->prepare($optSql);
        $optStmt->execute([':product_id' => $productId]);

        $product['options']    = $optStmt->fetchAll();
        $product['base_price'] = (float)$product['base_price'];

        return $product;
    }

    /**
     * Точка входа для AJAX-запроса: возвращает JSON-ответ с данными товара.
     * Вызывается из маршрутизатора при GET /api/product/{id}
     *
     * @param int $productId
     * @return void  Выводит JSON напрямую
     */
    public function apiGetProduct(int $productId): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $locale  = $_GET['locale'] ?? 'ru';
        $product = $this->getProductById($productId, $locale);

        if ($product === null) {
            http_response_code(404);
            echo json_encode(['error' => 'Товар не найден'], JSON_UNESCAPED_UNICODE);
            return;
        }

        echo json_encode($product, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
