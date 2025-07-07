<?php

declare(strict_types=1);

namespace WebDevProject\config;

class Config
{
    /**
     * @return string
     */
    public static function dbHost(): string
    {
        return $_ENV['DB_HOST'] ?? 'localhost';
    }

    /**
     * @return string
     */
    public static function dbUser(): string
    {
        return $_ENV['DB_USER'] ?? 'root';
    }

    /**
     * @return string
     */
    public static function dbPass(): string
    {
        return $_ENV['DB_PASS'] ?? '';
    }

    /**
     * @return string
     */
    public static function dbName(): string
    {
        return $_ENV['DB_NAME'] ?? 'recipe';
    }

    /**
     * @return string
     */
    public static function mailHost(): string
    {
        return $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
    }

    /**
     * @return string
     */
    public static function mailPort(): int
    {
        return (int)($_ENV['MAIL_PORT'] ?? 465);
    }

    /**
     * @return string
     */
    public static function mailUser(): string
    {
        return $_ENV['MAIL_USER'] ?? 'vassrichard31@gmail.com';
    }

    /**
     * @return string
     */
    public static function mailPass(): string
    {
        return $_ENV['MAIL_PASSWORD'] ?? 'itqc tbnx zlup ynpj';
    }

    /**
     * @return string
     */
    public static function mailFromAddress(): string
    {
        return $_ENV['MAIL_FROM_ADDRESS'] ?? 'feri@feri.com';
    }

    /**
     * @return string
     */
    public static function mailFromName(): string
    {
        return $_ENV['MAIL_FROM_NAME'] ?? 'FeriWebDevProject';
    }

    /**
     * @return string
     */
    public static function baseUrl(): string
    {
        return $_ENV['BASE_URL'] ?? 'http://feriwebdevproject';
    }
}
