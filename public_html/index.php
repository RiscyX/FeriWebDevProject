<?php
declare(strict_types=1);
session_start();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../src/config/db_config.php';

use WebDevProject\Controller\AdminController;
use WebDevProject\Controller\AuthController;
use WebDevProject\Controller\HomeController;

$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');   //  pl.  "/FeriWebDevProject"
$request = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$path = ($base && str_starts_with($request, $base))
    ? substr($request, strlen($base)) ?: '/'
    : ($request ?: '/');


$home = new HomeController($pdo);
$auth = new AuthController($pdo);
$admin = new AdminController($pdo);

match ($path) {
    '/'         => $home->index(),
    '/login'    => $auth->authLogin(),
    '/register' => $auth->authRegister(),
    '/verify'   => $auth->authVerify(),
    '/logout'   => $auth->authLogout(),
    '/reset'    => $auth->authPasswordReset(),

    '/admin'                => $admin->index(),
    '/admin/users'          => $admin->index(),
    default     => (function () {
        http_response_code(404);
        echo '404 – oldal nem található';
    })(),
};
