<?php
declare(strict_types=1);

session_start();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__.'/../vendor/autoload.php';

use Dotenv\Dotenv;
use FastRoute\RouteCollector;
use WebDevProject\Controller\Api\FridgeApiController;
use WebDevProject\Controller\Api\UserApiController;
use WebDevProject\Controller\FridgeController;
use WebDevProject\Controller\HomeController;
use WebDevProject\Controller\AuthController;
use WebDevProject\Controller\AdminController;
use WebDevProject\Controller\RecipeController;
use WebDevProject\Controller\ProfileController;
use WebDevProject\Controller\ErrorController;
use WebDevProject\Security\Csrf;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

require_once __DIR__.'/../src/config/db_config.php';

$dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r) {
    $r->addRoute('GET',  '/',             [HomeController::class, 'index']);

    // Auth
    $r->addRoute('GET',  '/login',        [AuthController::class, 'authLogin']);
    $r->addRoute('POST', '/login',        [AuthController::class, 'authLogin']);

    $r->addRoute('GET',  '/register',     [AuthController::class, 'authRegister']);
    $r->addRoute('POST', '/register',     [AuthController::class, 'authRegister']);

    $r->addRoute('GET',  '/verify',       [AuthController::class, 'authVerify']);

    $r->addRoute('GET',  '/logout',       [AuthController::class, 'authLogout']);
    $r->addRoute('POST', '/logout',       [AuthController::class, 'authLogout']);

    $r->addRoute('GET',  '/reset',        [AuthController::class, 'authPasswordReset']);
    $r->addRoute('POST', '/reset',        [AuthController::class, 'authPasswordReset']);

    $r->addRoute('GET',    '/fridge',           [FridgeController::class, 'index']);
    
    // Recipes
    $r->addRoute('GET',  '/recipes',         [RecipeController::class, 'index']);
    $r->addRoute('GET',  '/recipes/recommend', [RecipeController::class, 'recommend']);
    $r->addRoute('GET',  '/recipes/recommend/ai', [RecipeController::class, 'aiRecommend']);
    $r->addRoute('POST', '/recipes/save-ai-recipe', [RecipeController::class, 'saveAiRecipe']);
    $r->addRoute('GET',  '/recipe/{id:\d+}', [RecipeController::class, 'view']);
    $r->addRoute('GET',  '/recipe/submit',   [RecipeController::class, 'submitForm']);
    $r->addRoute('POST', '/recipe/submit',   [RecipeController::class, 'submitProcess']);
    
    // Profil útvonalak
    $r->addRoute('GET',  '/profile',      [ProfileController::class, 'index']);
    $r->addRoute('POST', '/profile/favorites/add', [ProfileController::class, 'addToFavorites']);
    $r->addRoute('POST', '/profile/favorites/remove', [ProfileController::class, 'removeFromFavorites']);

    $r->addGroup('/api/fridge', function(RouteCollector $r) {
        $r->addRoute('GET',   '',             [FridgeApiController::class, 'getItems']);
        $r->addRoute('POST',  '',             [FridgeApiController::class, 'addItem']);
        $r->addRoute('PUT',    '/{id:\d+}', [FridgeApiController::class, 'updateItem']);
        $r->addRoute('DELETE','/{id:\d+}',    [FridgeApiController::class, 'deleteItem']);
        $r->addRoute('GET',    '/user/{userId:\d+}', [FridgeApiController::class, 'getItems']);
    });
    $r->addRoute('GET',  '/api/ingredients', [FridgeApiController::class, 'searchIngredients']);
    $r->addRoute('GET',  '/api/user/status', [UserApiController::class, 'getStatus']);

    // Test útvonalak hibakezeléshez
    $r->addRoute('GET', '/test-403', [ErrorController::class, 'forbidden']);
    $r->addRoute('GET', '/test-405', [ErrorController::class, 'methodNotAllowed']);


    $r->addGroup('/admin', function (RouteCollector $r) {
        $r->addRoute('GET',  '',          [AdminController::class, 'index']);
        $r->addRoute('GET',  '/users',    [AdminController::class, 'index']);
        $r->addRoute('POST', '/users/ban', [AdminController::class, 'banUser']);
        $r->addRoute('POST', '/users/unban', [AdminController::class, 'unbanUser']);
        
        // Recept admin útvonalak
        $r->addRoute('GET',  '/recipes',    [AdminController::class, 'recipes']);
        $r->addRoute('POST', '/recipes/approve', [AdminController::class, 'approveRecipe']);
        $r->addRoute('POST', '/recipes/reject', [AdminController::class, 'rejectRecipe']);
    });
});

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri        = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
if ($basePath !== '' && str_starts_with($uri, $basePath)) {
    $uri = substr($uri, strlen($basePath)) ?: '/';
}

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        $errorController = new ErrorController($pdo);
        $errorController->notFound();
        break;

    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $errorController = new ErrorController($pdo);
        $errorController->methodNotAllowed();
        break;

    case FastRoute\Dispatcher::FOUND:
        [$class, $method] = $routeInfo[1];   // [Controller osztály, metódus]
        $vars = $routeInfo[2];               // útvonal‑paraméterek tömbje

        try {
            // Egyszerű dependency‑injection; válts DI‑konténerre, ha bővül a projekt
            $controller = new $class($pdo);
            $controller->$method(...array_values($vars));
            
            // Ellenőrizzük a response code-ot a végrehajtás után
            if (http_response_code() === 403) {
                $errorController = new ErrorController($pdo);
                $errorController->forbidden();
            }
        } catch (Exception $e) {
            // Ha 403-as hiba történik, vagy bármilyen access denied típusú hiba
            if (http_response_code() === 403 || str_contains($e->getMessage(), 'access') || str_contains($e->getMessage(), 'forbidden')) {
                $errorController = new ErrorController($pdo);
                $errorController->forbidden();
            } else {
                // Egyéb hibák esetén dobja újra
                throw $e;
            }
        }
        break;
}
