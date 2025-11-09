<?php

declare(strict_types=1);

namespace WebDevProject\Core;

class Auth
{
    /**
     * @return bool
     */
    public static function check(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    /**
     * @param \PDO $pdo
     * @return bool
     */
    public static function checkBanned(\PDO $pdo): bool
    {
        if (!self::check()) {
            return false;
        }

        $userId = (int)$_SESSION['user_id'];
        $stmt = $pdo->prepare("SELECT is_banned FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $user && (int)($user['is_banned'] ?? 0) === 1;
    }

    /**
     * @return void
     */
    public static function requireLogin(): void
    {
        global $pdo;

        if (!self::check()) {
            http_response_code(401);
            exit('Bejelentkezés szükséges');
        }

        // If the user is banned, log them out
        if (self::checkBanned($pdo)) {
            session_destroy();
            header('Location: /login?banned=1');
            exit('A fiókja bannolva lett. Kérjük, vegye fel a kapcsolatot az adminisztrátorral.');
        }
    }

    /**
     * @param int $role
     * @return void
     * @throws \Exception
     */
    public static function requireRole(int $role): void
    {
        if (!self::check() || ($_SESSION['role'] ?? '') !== $role) {
            http_response_code(403);
            throw new \Exception('Hozzáférés megtagadva - nincs megfelelő jogosultság');
        }
    }
}
