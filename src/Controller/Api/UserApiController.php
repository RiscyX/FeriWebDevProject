<?php

declare(strict_types=1);

namespace WebDevProject\Controller\Api;

use WebDevProject\Core\Auth;

class UserApiController
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /** JSON választ küld, kilép a végén */
    private function json(mixed $data, int $code = 200): never
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * GET /api/user/status
     * Visszaadja a bejelentkezett felhasználó állapotát
     */
    public function getStatus(): never
    {
        if (!isset($_SESSION['user_id'])) {
            $this->json(['error' => 'Nem vagy bejelentkezve'], 401);
        }

        $userId = (int)$_SESSION['user_id'];
        $stmt = $this->pdo->prepare("
            SELECT id, username, email, role, is_banned
            FROM users 
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            $this->json(['error' => 'Felhasználó nem található'], 404);
        }

        $this->json([
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => (int)$user['role'],
            'is_banned' => (bool)(int)$user['is_banned']
        ]);
    }
}
