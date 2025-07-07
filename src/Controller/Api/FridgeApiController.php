<?php

declare(strict_types=1);

namespace WebDevProject\Controller\Api;

use WebDevProject\Core\Auth;
use WebDevProject\Model\FridgeItem;

class FridgeApiController
{
    /**
     * @var \PDO
     */
    private \PDO $pdo;

    /**
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Send the items back in json format.
     * @param mixed $data
     * @param int $code
     * @return never
     */
    private function json(mixed $data, int $code = 200): never
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * GET /api/fridge
     * Gets the current users fridge items
     */
    public function getItems(int $userId): never
    {
        $items = FridgeItem::getByUser($this->pdo, $userId);
        $this->json(['data' => $items]);
    }

    /**
     * POST /api/fridge
     * Test = { name, quantity, expiry? }
     */
    public function addItem(): never
    {
        if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
            $payload = json_decode(file_get_contents('php://input'), true);
        } else {
            $payload = $_POST;
        }

        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($payload['csrf'] ?? '');
        if (!\WebDevProject\Security\Csrf::check($token)) {
            $this->json(['error' => 'CSRF token invalid'], 419);
        }

        $userId       = (int)($_SESSION['user_id'] ?? 0);
        $ingredientId = (int)($payload['ingredient_id'] ?? 0);
        $quantity     = (int)($payload['quantity'] ?? 0);

        if ($ingredientId < 1 || $quantity < 1) {
            $this->json(['error' => 'Hibás bemenet'], 400);
        }

        $newId = FridgeItem::create($this->pdo, [
            'user_id'      => $userId,
            'ingredient_id' => $ingredientId,
            'quantity'     => $quantity,
        ]);

        $this->json(['id' => $newId], 201);
    }

    /**
     * Updates the items.
     * @param string $id
     * @return never
     */
    public function updateItem(string $id): never
    {
        $id = (int)$id;
        $payload = json_decode(file_get_contents('php://input'), true);
        $token   = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!\WebDevProject\Security\Csrf::check($token)) {
            $this->json(['error' => 'CSRF token invalid'], 419);
        }

        $ingredientId = (int)($payload['ingredient_id'] ?? 0);
        $quantity     = (int)($payload['quantity'] ?? 0);
        if ($ingredientId < 1 || $quantity < 1) {
            $this->json(['error' => 'Invalid input'], 400);
        }

        $ok = FridgeItem::update($this->pdo, $id, [
            'ingredient_id' => $ingredientId,
            'quantity'      => $quantity,
        ]);

        $this->json(['updated' => (bool)$ok]);
    }

    /**
     * Deletes the selected item.
     * DELETE /api/fridge/{id}
     */
    public function deleteItem(string $id): never
    {
        $id = (int)$id;
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!\WebDevProject\Security\Csrf::check($token)) {
            $this->json(['error' => 'CSRF token invalid'], 419);
        }

        $ok = FridgeItem::delete($this->pdo, $id);
        $this->json(['deleted' => (bool)$ok]);
    }

    /**
     * GET /api/ingredients?search=csirke
     */
    public function searchIngredients(): never
    {
        $q = trim($_GET['search'] ?? '');
        if (mb_strlen($q) < 2) {
            $this->json(['data' => []]);
        }

        $stmt = $this->pdo->prepare("
            SELECT i.id, i.name, u.abbreviation AS unit_abbr, u.name AS unit_name
            FROM ingredients i
            JOIN units u ON u.id = i.unit_id
            WHERE i.name LIKE ?
            ORDER BY i.name
            LIMIT 10
        ");
        $stmt->execute(["%$q%"]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->json(['data' => $results]);
    }
}
