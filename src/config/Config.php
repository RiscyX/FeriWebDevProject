<?php

namespace WebDevProject\Config;

class Config
{
    public static function dbHost(): string
    {
        return $_ENV['DB_HOST'] ?? 'localhost';
    }

    public static function dbUser(): string
    {
        return $_ENV['DB_USER'] ?? 'root';
    }

    public static function dbPass(): string
    {
        return $_ENV['DB_PASS'] ?? '';
    }

    public static function dbName(): string
    {
        return $_ENV['DB_NAME'] ?? 'recipe';
    }

    public static function mailHost(): string
    {
        return $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
    }

    public static function mailPort(): int
    {
        return (int)($_ENV['MAIL_PORT'] ?? 465);
    }

    public static function mailUser(): string
    {
        return $_ENV['MAIL_USER'] ?? 'vassrichard31@gmail.com';
    }

    public static function mailPass(): string
    {
        return $_ENV['MAIL_PASSWORD'] ?? 'itqc tbnx zlup ynpj';
    }
    
    public static function mailFromAddress(): string
    {
        return $_ENV['MAIL_FROM_ADDRESS'] ?? 'feri@feri.com';
    }
    
    public static function mailFromName(): string
    {
        return $_ENV['MAIL_FROM_NAME'] ?? 'FeriWebDevProject';
    }
    
    public static function baseUrl(): string
    {
        return $_ENV['BASE_URL'] ?? 'http://feriwebdevproject';
    }
}
