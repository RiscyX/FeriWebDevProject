<?php

declare(strict_types=1);

namespace WebDevProject\Helper;

use WebDevProject\Security\Csrf;

/**
 * CSRF protection helper class
 * Standardizes CSRF error handling across controllers
 */
class CsrfHelper
{
    /**
     * Validates CSRF token and handles errors consistently
     *
     * @param string|null $token The CSRF token to validate
     * @param string $redirectUrl Where to redirect if validation fails
     * @param string $errorMessage Custom error message to display
     * @return bool True if validation passed
     */
    public static function validate(
        ?string $token,
        string $redirectUrl,
        string $errorMessage = 'Invalid CSRF token. Please try again.'
    ): bool {
        if (!$token || !Csrf::check($token)) {
            $_SESSION['flash_error'] = $errorMessage;
            header("Location: $redirectUrl");
            exit;
        }

        return true;
    }

    /**
     * Validates CSRF token for API endpoints and handles errors consistently
     *
     * @param string|null $token The CSRF token to validate
     * @return bool True if validation passed
     */
    public static function validateApi(?string $token): bool
    {
        if (!$token || !Csrf::check($token)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Invalid CSRF token'
            ]);
            exit;
        }

        return true;
    }
}
