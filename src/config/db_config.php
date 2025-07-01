<?php

use WebDevProject\config\Config;

try {
    $dsn = 'mysql:host=' . Config::dbHost() . ';dbname=' . Config::dbName() . ';charset=utf8mb4';
    $pdo = new PDO($dsn, Config::dbUser(), Config::dbPass());
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    exit;
}
