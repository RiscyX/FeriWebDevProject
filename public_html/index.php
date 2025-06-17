<!DOCTYPE html>
<html lang="hu" data-bs-theme="<?= htmlspecialchars($_COOKIE['theme'] ?? 'light') ?>">
<head>
    <meta charset="UTF-8">
    <title>Hűtőszekrényem</title>
    <link rel="stylesheet" href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/global.css">
    <link rel="stylesheet" href="./css/index.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="favicon.png" type="image/png">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
</head>
<body>

 <?php require_once "../src/View/partials/nav.php"; ?>

<main class="container py-3">
    <section class="hero mt-4 mb-5">
        <h1 class="hero-title">Üdvözöl a Hűtőszekrényem!</h1>
        <p class="hero-desc">Regisztrálj, és egy kattintással átláthatod, mi vár rád a hűtőszekrényed polcain, és megtervezheted a kajádat pillanatok alatt.</p>
        <a href="register.php" class="btn btn-primary">Regisztrálj most</a>
    </section>
    <div class="divider"></div>
    <section class="cards mb-5">
        <div class="card">
            <h2 class="card-title">Könnyű kezelés</h2>
            <p class="card-desc">Gyorsan hozzáadhatod, szerkesztheted vagy törölheted a hűtődben lévő termékeket, akár mobilról is.</p>
        </div>
        <div class="card">
            <h2 class="card-title">Lejárati figyelmeztetés</h2>
            <p class="card-desc">Automatikus értesítések a közelgő lejáratokról, hogy semmi ne vesszen kárba.</p>
        </div>
        <div class="card">
            <h2 class="card-title">Zöld szemlélet</h2>
            <p class="card-desc">A dizájn és a funkciók is a fenntarthatóságot és a környezettudatosságot támogatják.</p>
        </div>
    </section>
    <?php require_once '../src/View/partials/carousel.php'; ?>
</main>

<script src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="./js/theme-toggle.js"></script>
</body>
</html>
<?php
declare(strict_types=1);
session_start();

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../src/config/db_config.php';

use WebDevProject\Controller\AuthController;
use WebDevProject\Controller\HomeController;

$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');   //  pl.  "/FeriWebDevProject"
$request = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$path = ($base && str_starts_with($request, $base))
    ? substr($request, strlen($base)) ?: '/'
    : ($request ?: '/');


$home = new HomeController($pdo);
$auth = new AuthController($pdo);

switch ($path) {
    case '/':
        $home->index();        // betölti a home view-t
        break;
    case '/login':
        $auth->authLogin();
        break;
    case '/register':
        $auth->authRegister();
        break;
    case '/verify':
        $auth->authVerify();
        break;
    case '/logout':
        $auth->authLogout();
        break;
    default:
        http_response_code(404);
        echo '404 – oldal nem található';
}
