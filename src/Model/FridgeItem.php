<?php

declare(strict_types=1);

namespace WebDevProject\Model;

use PDO;

class FridgeItem
{
    /**
     * Get all items for a user
     * @param PDO $pdo
     * @param int $userId
     * @return array<string, mixed>[]
     */
    public static function getByUser(PDO $pdo, int $userId): array
    {
        $stmt = $pdo->prepare(
            'SELECT id, ingredient_id ,ingredient_name, quantity, unit_name, unit_abbr
     FROM fridge_items_view
     WHERE user_id = :uid'
        );
        $stmt->execute(['uid' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get item data by id
     * @param PDO $pdo
     * @param int $id
     * @return array<string, mixed>|null
     */
    public static function find(PDO $pdo, int $id): ?array
    {
        $sql = 'SELECT id, user_id, name, quantity, expiry, created_at
                FROM fridge_items
                WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        return $item !== false ? $item : null;
    }

    /**
     * Check if a user already has a specific ingredient in their fridge
     * @param PDO $pdo
     * @param int $userId
     * @param int $ingredientId
     * @return array|null Item data if it exists, null if not
     */
    public static function findByIngredient(PDO $pdo, int $userId, int $ingredientId): ?array
    {
        $sql = 'SELECT id, user_id, ingredient_id, quantity
                FROM fridge_items
                WHERE user_id = :user_id AND ingredient_id = :ingredient_id
                LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'ingredient_id' => $ingredientId
        ]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        return $item !== false ? $item : null;
    }

    /**
     * Create a new item or increase the quantity of an existing one
     * @param PDO $pdo
     * @param array<string, mixed> $data [user_id, name, quantity, expiry]
     * @return int new record id or updated record id
     * @throws \InvalidArgumentException if the data is invalid
     */
    public static function create(PDO $pdo, array $data): int
    {
        // Validate input data
        self::validateInputData($data);

        // First check if this ingredient already exists in the user's fridge
        $existingItem = self::findByIngredient(
            $pdo,
            $data['user_id'],
            $data['ingredient_id']
        );

        if ($existingItem) {
            // If it already exists, increase the quantity
            $newQuantity = $existingItem['quantity'] + $data['quantity'];
            self::update($pdo, (int)$existingItem['id'], [
                'ingredient_id' => $data['ingredient_id'],
                'quantity' => $newQuantity
            ]);
            return (int)$existingItem['id'];
        } else {
            // If it doesn't exist yet, create a new record
            $sql = 'INSERT INTO fridge_items (user_id, ingredient_id, quantity)
                VALUES (:user_id, :ingredient_id, :quantity)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'user_id' => $data['user_id'],
                'ingredient_id' => $data['ingredient_id'],
                'quantity' => $data['quantity'],
            ]);
            return (int)$pdo->lastInsertId();
        }
    }


    /**
     * Update an item
     * @param PDO $pdo
     * @param int $id
     * @param array<string, mixed> $data [name, quantity, expiry]
     * @return bool
     * @throws \InvalidArgumentException if the data is invalid
     */
    public static function update(PDO $pdo, int $id, array $data): bool
    {
        // Validate input data
        self::validateInputData($data);

        $sql = 'UPDATE fridge_items
                SET ingredient_id = :ingredient_id,
                    quantity = :quantity
                WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'ingredient_id' => $data['ingredient_id'],
            'quantity' => $data['quantity'],
            'id' => $id,
        ]);
    }

    /**
     * Delete an item
     * @param PDO $pdo
     * @param int $id
     * @return bool
     */
    public static function delete(PDO $pdo, int $id): bool
    {
        $sql = 'DELETE FROM fridge_items WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Validate input data for create and update operations
     *
     * @param array<string, mixed> $data The data to validate
     * @throws \InvalidArgumentException If the data is invalid
     */
    private static function validateInputData(array $data): void
    {
        // Validate user_id if present
        if (isset($data['user_id'])) {
            if (!is_numeric($data['user_id']) || (int)$data['user_id'] <= 0) {
                throw new \InvalidArgumentException("Invalid user ID");
            }
        }

        // Validate ingredient_id
        if (!isset($data['ingredient_id']) || !is_numeric($data['ingredient_id']) || (int)$data['ingredient_id'] <= 0) {
            throw new \InvalidArgumentException("Invalid ingredient ID");
        }

        // Validate quantity
        if (!isset($data['quantity']) || !is_numeric($data['quantity'])) {
            throw new \InvalidArgumentException("Quantity must be a number");
        }

        $quantity = (float)$data['quantity'];
        if ($quantity <= 0) {
            throw new \InvalidArgumentException("Quantity must be greater than 0");
        }

        if ($quantity > 10000) {
            throw new \InvalidArgumentException("Quantity is too large (max: 10000)");
        }
    }
}
