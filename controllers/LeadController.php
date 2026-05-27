<?php
/**
 * LeadController.php
 *
 * Приём заявок на коммерческое предложение (лиды).
 * CSRF и санитизация — по паттерну OrderController.
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use InvalidArgumentException;
use PDO;
use RuntimeException;

class LeadController
{
    private PDO $db;

    private const ALLOWED_SOURCES = ['home', 'cases', 'contacts', 'product'];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->startSecureSession();
    }

    private function startSecureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $config = require __DIR__ . '/../config/config.php';
            $sess   = $config['session'];

            session_set_cookie_params([
                'lifetime' => $sess['lifetime'],
                'path'     => '/',
                'domain'   => '',
                'secure'   => $sess['secure'],
                'httponly' => $sess['httponly'],
                'samesite' => $sess['samesite'],
            ]);

            session_start();
        }
    }

    public function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;

        return $token;
    }

    private function validateCsrfToken(string $tokenFromRequest): void
    {
        $sessionToken = $_SESSION['csrf_token'] ?? '';

        if (
            empty($sessionToken) ||
            !hash_equals($sessionToken, $tokenFromRequest)
        ) {
            throw new RuntimeException('Недействительный CSRF-токен.', 403);
        }

        unset($_SESSION['csrf_token']);
    }

    /**
     * POST /api/lead/create
     */
    public function createLead(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Метод не разрешён.'],
                JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $rawBody = file_get_contents('php://input');
            $data    = json_decode($rawBody, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                throw new InvalidArgumentException('Некорректный формат JSON в теле запроса.');
            }

            $csrfToken = $this->sanitizeString($data['csrf_token'] ?? '');
            $this->validateCsrfToken($csrfToken);

            $lead = $this->validateLead($data);
            $leadId = $this->persistLead($lead);

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'id'      => $leadId,
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
            error_log('[LeadController] Unexpected error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Внутренняя ошибка сервера.'],
                JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, string|null>
     */
    private function validateLead(array $data): array
    {
        $name = $this->sanitizeString($data['name'] ?? '', 150);
        if (mb_strlen($name) < 2 || mb_strlen($name) > 150) {
            throw new InvalidArgumentException('Укажите корректное имя (2–150 символов).');
        }

        $email = trim((string)($data['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Укажите корректный email.');
        }
        $email = $this->sanitizeString($email, 255);

        $phoneRaw = trim((string)($data['phone'] ?? ''));
        $phoneDigits = preg_replace('/\D/', '', $phoneRaw) ?? '';
        if (strlen($phoneDigits) < 10) {
            throw new InvalidArgumentException('Укажите корректный номер телефона (минимум 10 цифр).');
        }
        $phone = $this->sanitizeString($phoneRaw, 50);

        $organization = $this->sanitizeString($data['organization'] ?? '', 200);
        $organization = $organization !== '' ? $organization : null;

        $comment = $this->sanitizeString($data['comment'] ?? '', 2000);
        $comment = $comment !== '' ? $comment : null;

        $source = $this->sanitizeString($data['source'] ?? 'home', 20);
        if (!in_array($source, self::ALLOWED_SOURCES, true)) {
            $source = 'home';
        }

        return [
            'email'        => $email,
            'name'         => $name,
            'phone'        => $phone,
            'organization' => $organization,
            'comment'      => $comment,
            'source'       => $source,
        ];
    }

    /**
     * @param array<string, string|null> $lead
     */
    private function persistLead(array $lead): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO leads (email, name, phone, organization, comment, source, status, created_at)
            VALUES (:email, :name, :phone, :organization, :comment, :source, 'new', NOW())
        ");

        $stmt->execute([
            ':email'        => $lead['email'],
            ':name'         => $lead['name'],
            ':phone'        => $lead['phone'],
            ':organization' => $lead['organization'],
            ':comment'      => $lead['comment'],
            ':source'       => $lead['source'],
        ]);

        return (int)$this->db->lastInsertId();
    }

    private function sanitizeString(mixed $value, int $maxLen = 500): string
    {
        if (!is_string($value) && !is_numeric($value)) {
            return '';
        }

        $clean = trim((string)$value);
        $clean = mb_substr($clean, 0, $maxLen);

        return htmlspecialchars($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
