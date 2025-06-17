<?php
// public_html/logout.php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config/db_config.php';

use WebDevProject\Controller\AuthController;

(new AuthController($pdo))->authLogout();

