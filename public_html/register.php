<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config/db_config.php';

use WebDevProject\Controller\AuthController;

(new AuthController($pdo))->register();
