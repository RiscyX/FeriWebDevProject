<?php

namespace WebDevProject\Model;

use PDO;

class FridgeItem
{
    /**
     * Lekéri a felhasználó összes elemét
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
     * Lekéri egy tétel adatait id alapján
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
     * Ellenőrzi, hogy egy felhasználónak van-e már adott összetevőből a hűtőjében
     * @param PDO $pdo
     * @param int $userId
     * @param int $ingredientId
     * @return array|null Az elem adatai, ha létezik, null ha nem
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
     * Új tétel létrehozása vagy meglévő mennyiségének növelése
     * @param PDO $pdo
     * @param array<string, mixed> $data [user_id, name, quantity, expiry]
     * @return int új rekord id vagy a frissített rekord id-ja
     */
    public static function create(PDO $pdo, array $data): int
    {
        // Először ellenőrizzük, hogy van-e már ilyen összetevő a felhasználó hűtőjében
        $existingItem = self::findByIngredient(
            $pdo,
            $data['user_id'],
            $data['ingredient_id']
        );

        if ($existingItem) {
            // Ha már létezik, növeljük a mennyiségét
            $newQuantity = $existingItem['quantity'] + $data['quantity'];
            self::update($pdo, (int)$existingItem['id'], [
                'ingredient_id' => $data['ingredient_id'],
                'quantity' => $newQuantity
            ]);
            return (int)$existingItem['id'];
        } else {
            // Ha még nem létezik, új rekordot hozunk létre
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
     * Tétel frissítése
     * @param PDO $pdo
     * @param int $id
     * @param array<string, mixed> $data [name, quantity, expiry]
     * @return bool
     */
    public static function update(PDO $pdo, int $id, array $data): bool
    {
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
     * Tétel törlése
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
}
