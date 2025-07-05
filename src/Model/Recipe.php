<?php

namespace WebDevProject\Model;

class Recipe
{
    /**
     * Új recept létrehozása
     *
     * @param \PDO $pdo
     * @param array $data Recept adatai
     * @return int Az új recept azonosítója
     */
    public static function create(\PDO $pdo, array $data): int
    {
        $stmt = $pdo->prepare("
            INSERT INTO recipes (
                user_id,
                title,
                description,
                instructions,
                prep_time,
                cook_time,
                servings,
                category_id
            ) VALUES (
                :user_id,
                :title,
                :description,
                :instructions,
                :prep_time,
                :cook_time,
                :servings,
                :category_id
            )
        ");

        $stmt->execute([
            'user_id'      => $data['user_id'],
            'title'        => $data['title'],
            'description'  => $data['description'],
            'instructions' => $data['instructions'],
            'prep_time'    => $data['prep_time'] ?? null,
            'cook_time'    => $data['cook_time'] ?? null,
            'servings'     => $data['servings'] ?? null,
            'category_id'  => $data['category_id'] ?? null
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Hozzávalók mentése egy recepthez
     *
     * @param \PDO $pdo
     * @param int $recipeId Recept azonosító
     * @param array $ingredients Hozzávalók
     * @return bool Sikeres volt-e a mentés
     */
    public static function saveIngredients(\PDO $pdo, int $recipeId, array $ingredients): bool
    {
        $stmt = $pdo->prepare("
            INSERT INTO recipe_ingredients (
                recipe_id,
                ingredient_id,
                quantity
            ) VALUES (
                :recipe_id,
                :ingredient_id,
                :quantity
            )
        ");

        foreach ($ingredients as $ingredient) {
            if (empty($ingredient['ingredient_id'])) {
                continue; // Átugorjuk a nem érvényes hozzávalókat
            }

            $stmt->execute([
                'recipe_id'     => $recipeId,
                'ingredient_id' => $ingredient['ingredient_id'],
                'quantity'      => $ingredient['quantity']
            ]);
        }

        return true;
    }

    /**
     * Recept lekérdezése azonosító alapján
     *
     * @param \PDO $pdo
     * @param int $id Recept azonosító
     * @return array|null Recept adatok vagy null ha nem létezik
     */
    public static function getById(\PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare("
            SELECT 
                r.*,
                u.username as created_by,
                c.name as category,
                rp.path as image_path
            FROM recipes r
            LEFT JOIN users u ON u.id = r.user_id
            LEFT JOIN categories c ON c.id = r.category_id
            LEFT JOIN recipe_picture rp ON rp.recipe_id = r.id
            WHERE r.id = :id
        ");

        $stmt->execute(['id' => $id]);
        $recipe = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$recipe) {
            return null;
        }

        // Hozzávalók lekérdezése
        $stmt = $pdo->prepare("
            SELECT 
                ri.quantity,
                i.name as ingredient_name,
                u.name as unit_name,
                u.abbreviation as unit_abbr
            FROM recipe_ingredients ri
            JOIN ingredients i ON i.id = ri.ingredient_id
            JOIN units u ON u.id = i.unit_id
            WHERE ri.recipe_id = :recipe_id
        ");

        $stmt->execute(['recipe_id' => $id]);
        $recipe['ingredients'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $recipe;
    }

    /**
     * Összes jóváhagyott recept lekérése
     *
     * @param \PDO $pdo
     * @param array $options Opciók (limit, offset, stb.)
     * @return array Receptek tömbje
     */
    public static function getAll(\PDO $pdo, array $options = []): array
    {
        $limit = isset($options['limit']) ? (int)$options['limit'] : 12;
        $offset = isset($options['offset']) ? (int)$options['offset'] : 0;

        $stmt = $pdo->prepare("
            SELECT 
                r.*,
                u.username as created_by,
                c.name as category,
                rp.path as image_path
            FROM recipes r
            LEFT JOIN users u ON u.id = r.user_id
            LEFT JOIN categories c ON c.id = r.category_id
            LEFT JOIN recipe_picture rp ON rp.recipe_id = r.id
            WHERE r.verified_at IS NOT NULL
            ORDER BY r.created_at DESC
            LIMIT :limit OFFSET :offset
        ");

        $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Jóváhagyásra váró receptek lekérése
     *
     * @param \PDO $pdo
     * @param array $options Opciók (limit, offset, stb.)
     * @return array Receptek tömbje
     */
    public static function getPendingRecipes(\PDO $pdo, array $options = []): array
    {
        $limit = isset($options['limit']) ? (int)$options['limit'] : 50;
        $offset = isset($options['offset']) ? (int)$options['offset'] : 0;

        $stmt = $pdo->prepare("
            SELECT 
                r.*,
                u.username as created_by,
                c.name as category,
                rp.path as image_path
            FROM recipes r
            LEFT JOIN users u ON u.id = r.user_id
            LEFT JOIN categories c ON c.id = r.category_id
            LEFT JOIN recipe_picture rp ON rp.recipe_id = r.id
            WHERE r.verified_at IS NULL
            ORDER BY r.created_at ASC
            LIMIT :limit OFFSET :offset
        ");

        $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Kategória azonosító lekérése név alapján vagy új kategória létrehozása
     *
     * @param \PDO $pdo
     * @param string $categoryName Kategória neve
     * @return int Kategória azonosító
     */
    public static function getCategoryId(\PDO $pdo, string $categoryName): int
    {
        // Először keressük meg a kategóriát
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = :name");
        $stmt->execute(['name' => $categoryName]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($result) {
            return (int)$result['id'];
        }

        // Ha nem létezik, hozzuk létre
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (:name)");
        $stmt->execute(['name' => $categoryName]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Recept jóváhagyása
     *
     * @param \PDO $pdo
     * @param int $recipeId A recept azonosítója
     * @return bool Sikeres volt-e a jóváhagyás
     */
    public static function approveRecipe(\PDO $pdo, int $recipeId): bool
    {
        $stmt = $pdo->prepare("
            UPDATE recipes 
            SET verified_at = NOW() 
            WHERE id = :id AND verified_at IS NULL
        ");

        $stmt->execute(['id' => $recipeId]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Recept elutasítása (törlése)
     *
     * @param \PDO $pdo
     * @param int $recipeId A recept azonosítója
     * @return bool Sikeres volt-e az elutasítás
     */
    public static function rejectRecipe(\PDO $pdo, int $recipeId): bool
    {
        // Előbb töröljük a képet
        $stmt = $pdo->prepare("DELETE FROM recipe_picture WHERE recipe_id = :id");
        $stmt->execute(['id' => $recipeId]);

        // Töröljük a hozzávalókat
        $stmt = $pdo->prepare("DELETE FROM recipe_ingredients WHERE recipe_id = :id");
        $stmt->execute(['id' => $recipeId]);

        // Végül a receptet
        $stmt = $pdo->prepare("DELETE FROM recipes WHERE id = :id AND verified_at IS NULL");
        $stmt->execute(['id' => $recipeId]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Összes kategória lekérdezése
     *
     * @param \PDO $pdo
     * @return array Kategóriák tömbje
     */
    public static function getAllCategories(\PDO $pdo): array
    {
        $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
