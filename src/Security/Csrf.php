<?php

declare(strict_types=1);

namespace WebDevProject\Security;

use Random\RandomException;

final class Csrf
{
    /**
     * @throws RandomException
     */
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * @param $token
     * @return bool
     */
    public static function check($token): bool
    {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        if (!is_string($token) || $token === '') {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * CSRF token input field generation.
     *
     * @return string
     */
    public static function field(): string
    {
        try {
            $token = self::token();
            return '<input type="hidden" name="csrf" value="' . htmlspecialchars($token) . '">';
        } catch (RandomException $e) {
            error_log('Hiba a CSRF token generálása közben: ' . $e->getMessage());
            return '<input type="hidden" name="csrf" value="">';
        }
    }
}
