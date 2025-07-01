<?php

namespace WebDevProject\Controller;

use GuzzleHttp\Client;
use PDO;
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
            'base_uri' => 'http://localhost',
            'cookies'  => true,
        ]);
    }

    /**
     * GET /fridge – a bejelentkezett felhasználó hűtője
     */
    public function index(): void
    {
        $userId = (int)($_SESSION['user_id'] ?? -1);
        $items  = FridgeItem::getByUser($this->pdo, $userId);
        $this->render(compact('items'));
    }

    /**
     * POST /fridge/delete - Hűtő elem törlése
     */
    public function delete(): void
    {
        // CSRF ellenőrzés
        if (!isset($_POST['csrf']) || !Csrf::check($_POST['csrf'])) {
            http_response_code(403);
            die('CSRF token mismatch');
        }

        // Ellenőrizd, hogy van-e ID
        if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
            $_SESSION['flash'] = 'Hiányzó vagy érvénytelen elem azonosító.';
            header('Location: /fridge');
            exit;
        }

        $id = (int)$_POST['id'];

        try {
            // API hívás az elem törlésére
            $response = $this->http->delete("/api/fridge/$id", [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            $_SESSION['flash'] = 'A tétel sikeresen törölve lett.';
        } catch (\Exception $e) {
            $_SESSION['flash'] = 'Hiba történt a törlés során: ' . $e->getMessage();
        }

        header('Location: /fridge');
        exit;
    }

    /**
     * Egyszerű nézet-render hívó
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
