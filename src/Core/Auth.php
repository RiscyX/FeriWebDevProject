<?php

namespace WebDevProject\Core;

class Auth
{
    public static function check(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public static function requireRole(int $role): void
    {
        if (!self::check() || ($_SESSION['role'] ?? '') !== $role) {
            http_response_code(403);
            exit('Hozzáférés megtagadva');
        }
    }
}
