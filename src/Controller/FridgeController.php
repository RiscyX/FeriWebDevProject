<?php

declare(strict_types=1);

namespace WebDevProject\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PDO;
use WebDevProject\Config\Config;
use WebDevProject\Core\Auth;
use WebDevProject\Model\FridgeItem;
use WebDevProject\Security\Csrf;

class FridgeController
{
    private Client $http;

    public function __construct(
        private PDO $pdo
    ) {
        Auth::requireLogin();
        $this->http = new Client([
            'base_uri' => Config::baseUrl(),
            'cookies'  => true,
        ]);
    }

    /**
     * GET /fridge – the logged in user's fridge.
     * @return void
     */
    public function index(): void
    {
        $userId = (int)($_SESSION['user_id'] ?? -1);
        $items  = FridgeItem::getByUser($this->pdo, $userId);
        $this->render(compact('items'));
    }

    /**
     * POST /fridge/delete - Delete fridge item.
     * @return void
     * @throws GuzzleException
     */
    public function delete(): void
    {
        // CSRF verification
        if (!isset($_POST['csrf']) || !Csrf::check($_POST['csrf'])) {
            http_response_code(403);
            die('CSRF token mismatch');
        }

        // Check if ID exists
        if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
            $_SESSION['flash'] = 'Missing or invalid item ID.';
            header('Location: /fridge');
            exit;
        }

        $id = (int)$_POST['id'];

        try {
            // API call to delete the item
            $response = $this->http->delete("/api/fridge/$id", [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            $_SESSION['flash'] = 'The item has been successfully deleted.';
        } catch (\Exception $e) {
            $_SESSION['flash'] = 'An error occurred during deletion: ' . $e->getMessage();
        }

        header('Location: /fridge');
        exit;
    }

    /**
     * Simple view-render caller
     * @param array $vars
     * @return void
     */
    private function render(array $vars = []): void
    {
        extract($vars, EXTR_SKIP);
        ob_start();
        include __DIR__ . "/../View/pages/fridge.php";
        $content = ob_get_clean();

        include __DIR__ . '/../View/layout.php';
    }
}
