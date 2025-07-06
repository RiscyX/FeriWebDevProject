<?php

declare(strict_types=1);

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

    /**
     * Egy recept hozzávalóinak lekérdezése
     *
     * @param \PDO $pdo
     * @param int $recipeId A recept azonosítója
     * @return array A recept hozzávalói
     */
    public static function getIngredients(\PDO $pdo, int $recipeId): array
    {
        $stmt = $pdo->prepare("
            SELECT 
                ri.ingredient_id,
                ri.quantity,
                i.name as ingredient_name,
                u.name as unit_name,
                u.abbreviation as unit_abbr
            FROM recipe_ingredients ri
            JOIN ingredients i ON i.id = ri.ingredient_id
            JOIN units u ON u.id = i.unit_id
            WHERE ri.recipe_id = :recipe_id
        ");

        $stmt->execute(['recipe_id' => $recipeId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Összes jóváhagyott recept lekérése a hozzávalókkal együtt
     *
     * @param \PDO $pdo
     * @return array Receptek tömbje hozzávalókkal
     */
    public static function getAllWithIngredients(\PDO $pdo): array
    {
        // Először lekérjük az összes receptet
        $recipes = self::getAll($pdo);

        if (empty($recipes)) {
            return [];
        }

        // Előfeldolgozzuk a recepteket, hogy legyen name és title mező is
        foreach ($recipes as &$recipe) {
            // Előkészítjük a name mezőt, ha még nincs
            if (!isset($recipe['name']) && isset($recipe['title'])) {
                $recipe['name'] = $recipe['title'];
            }

            // Alapértelmezett üres hozzávalók tömböt állítunk be
            $recipe['ingredients'] = [];
        }

        // Recept azonosítók kinyerése
        $recipeIds = array_column($recipes, 'id');

        if (empty($recipeIds)) {
            return $recipes;
        }

        $inClause = implode(',', array_fill(0, count($recipeIds), '?'));

        // Hozzávalók lekérdezése az összes recepthez
        $stmt = $pdo->prepare("
            SELECT 
                ri.recipe_id,
                ri.ingredient_id,
                ri.quantity,
                i.name as ingredient_name,
                u.name as unit_name,
                u.abbreviation as unit_abbr
            FROM recipe_ingredients ri
            JOIN ingredients i ON i.id = ri.ingredient_id
            JOIN units u ON u.id = i.unit_id
            WHERE ri.recipe_id IN ($inClause)
        ");

        // Paraméterek megadása a IN clause-hoz
        foreach ($recipeIds as $index => $id) {
            $stmt->bindValue($index + 1, $id, \PDO::PARAM_INT);
        }

        $stmt->execute();
        $ingredients = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Hozzávalók csoportosítása recept azonosító szerint
        $ingredientsByRecipe = [];
        foreach ($ingredients as $ingredient) {
            $recipeId = $ingredient['recipe_id'];
            if (!isset($ingredientsByRecipe[$recipeId])) {
                $ingredientsByRecipe[$recipeId] = [];
            }
            $ingredientsByRecipe[$recipeId][] = $ingredient;
        }

        // Hozzávalók hozzáadása a receptekhez
        foreach ($recipes as &$recipe) {
            $recipe['ingredients'] = $ingredientsByRecipe[$recipe['id']] ?? [];
        }

        return $recipes;
    }

    /**
     * Recept hozzáadása a kedvencekhez
     *
     * @param \PDO $pdo
     * @param int $userId A felhasználó azonosítója
     * @param int $recipeId A recept azonosítója
     * @return bool Sikeres volt-e a művelet
     */
    public static function addToFavorites(\PDO $pdo, int $userId, int $recipeId): bool
    {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO favorites (user_id, recipe_id)
                VALUES (:user_id, :recipe_id)
                ON DUPLICATE KEY UPDATE created_at = CURRENT_TIMESTAMP
            ");

            return $stmt->execute([
                'user_id' => $userId,
                'recipe_id' => $recipeId
            ]);
        } catch (\PDOException $e) {
            error_log('Hiba a kedvencekhez adás során: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Recept eltávolítása a kedvencekből
     *
     * @param \PDO $pdo
     * @param int $userId A felhasználó azonosítója
     * @param int $recipeId A recept azonosítója
     * @return bool Sikeres volt-e a művelet
     */
    public static function removeFromFavorites(\PDO $pdo, int $userId, int $recipeId): bool
    {
        try {
            $stmt = $pdo->prepare("
                DELETE FROM favorites
                WHERE user_id = :user_id AND recipe_id = :recipe_id
            ");

            return $stmt->execute([
                'user_id' => $userId,
                'recipe_id' => $recipeId
            ]);
        } catch (\PDOException $e) {
            error_log('Hiba a kedvencekből törlés során: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Felhasználó kedvenc receptjeinek lekérése
     *
     * @param \PDO $pdo
     * @param int $userId A felhasználó azonosítója
     * @return array A kedvenc receptek listája
     */
    public static function getUserFavorites(\PDO $pdo, int $userId): array
    {
        try {
            $stmt = $pdo->prepare("
                SELECT 
                    r.*,
                    c.name as category,
                    u.username as created_by,
                    rp.path as image_path,
                    f.created_at as favorite_added_at
                FROM favorites f
                JOIN recipes r ON r.id = f.recipe_id
                LEFT JOIN categories c ON c.id = r.category_id
                LEFT JOIN users u ON u.id = r.user_id
                LEFT JOIN recipe_picture rp ON rp.recipe_id = r.id
                WHERE f.user_id = :user_id
                ORDER BY f.created_at DESC
            ");

            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Hiba a kedvenc receptek lekérdezése során: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Ellenőrzi, hogy egy recept a felhasználó kedvence-e
     *
     * @param \PDO $pdo
     * @param int $userId A felhasználó azonosítója
     * @param int $recipeId A recept azonosítója
     * @return bool Kedvenc-e a recept
     */
    public static function isFavorite(\PDO $pdo, int $userId, int $recipeId): bool
    {
        try {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count
                FROM favorites
                WHERE user_id = :user_id AND recipe_id = :recipe_id
            ");

            $stmt->execute([
                'user_id' => $userId,
                'recipe_id' => $recipeId
            ]);

            return (int)$stmt->fetch(\PDO::FETCH_ASSOC)['count'] > 0;
        } catch (\PDOException $e) {
            error_log('Hiba a kedvenc ellenőrzés során: ' . $e->getMessage());
            return false;
        }
    }
}
