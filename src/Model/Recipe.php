<?php

declare(strict_types=1);

namespace WebDevProject\Model;

class Recipe
{
    /**
     * Create new recipe
     *
     * @param \PDO $pdo
     * @param array $data Recipe data
     * @return int The ID of the new recipe
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
     * Save ingredient to a recipe.
     *
     * @param \PDO $pdo
     * @param int $recipeId
     * @param array $ingredients
     * @return bool
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
                continue; // Skip invalid ingredients
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
     * Gets the recipe depending on the recipe id.
     *
     * @param \PDO $pdo
     * @param int $id
     * @return array|null
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

        // Query ingredients
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
     * Get all approved recipes
     *
     * @param \PDO $pdo
     * @param array $options Options (limit, offset, etc.)
     * @return array Array of recipes
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
     * Returns the pending recipes.
     *
     * @param \PDO $pdo
     * @param array $options
     * @return array
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
     * Return the categories by id.
     *
     * @param \PDO $pdo
     * @param string $categoryName Category name
     * @return int Category identifier
     */
    public static function getCategoryId(\PDO $pdo, string $categoryName): int
    {
        // First, look for the category
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = :name");
        $stmt->execute(['name' => $categoryName]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($result) {
            return (int)$result['id'];
        }

        // If it doesn't exist, create it
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (:name)");
        $stmt->execute(['name' => $categoryName]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Approve recipes.
     *
     * @param \PDO $pdo
     * @param int $recipeId
     * @return bool
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
     * Deny recipes.
     *
     * @param \PDO $pdo
     * @param int $recipeId
     * @return bool
     */
    public static function rejectRecipe(\PDO $pdo, int $recipeId): bool
    {
        $stmt = $pdo->prepare("DELETE FROM recipe_picture WHERE recipe_id = :id");
        $stmt->execute(['id' => $recipeId]);

        $stmt = $pdo->prepare("DELETE FROM recipe_ingredients WHERE recipe_id = :id");
        $stmt->execute(['id' => $recipeId]);

        $stmt = $pdo->prepare("DELETE FROM recipes WHERE id = :id AND verified_at IS NULL");
        $stmt->execute(['id' => $recipeId]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Get all categories
     *
     * @param \PDO $pdo
     * @return array Array of categories
     */
    public static function getAllCategories(\PDO $pdo): array
    {
        $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Return the ingredients for a recipe.
     *
     * @param \PDO $pdo
     * @param int $recipeId
     * @return array
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
     * Get all approved recipes with ingredients
     *
     * @param \PDO $pdo
     * @return array Array of recipes with ingredients
     */
    public static function getAllWithIngredients(\PDO $pdo): array
    {
        $recipes = self::getAll($pdo);

        if (empty($recipes)) {
            return [];
        }

        foreach ($recipes as &$recipe) {
            if (!isset($recipe['name']) && isset($recipe['title'])) {
                $recipe['name'] = $recipe['title'];
            }

            $recipe['ingredients'] = [];
        }

        // Extract recipe identifiers
        $recipeIds = array_column($recipes, 'id');

        if (empty($recipeIds)) {
            return $recipes;
        }

        $inClause = implode(',', array_fill(0, count($recipeIds), '?'));

        // Query ingredients for all recipes
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

        // Set parameters for the IN clause
        foreach ($recipeIds as $index => $id) {
            $stmt->bindValue($index + 1, $id, \PDO::PARAM_INT);
        }

        $stmt->execute();
        $ingredients = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Group ingredients by recipe identifier
        $ingredientsByRecipe = [];
        foreach ($ingredients as $ingredient) {
            $recipeId = $ingredient['recipe_id'];
            if (!isset($ingredientsByRecipe[$recipeId])) {
                $ingredientsByRecipe[$recipeId] = [];
            }
            $ingredientsByRecipe[$recipeId][] = $ingredient;
        }

        // Add ingredients to recipes
        foreach ($recipes as &$recipe) {
            $recipe['ingredients'] = $ingredientsByRecipe[$recipe['id']] ?? [];
        }

        return $recipes;
    }

    /**
     * Add recipe to favorites
     *
     * @param \PDO $pdo
     * @param int $userId The user identifier
     * @param int $recipeId The recipe identifier
     * @return bool Whether the operation was successful
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
            error_log('Error while adding to favorites: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove recipe from favorites
     *
     * @param \PDO $pdo
     * @param int $userId The user identifier
     * @param int $recipeId The recipe identifier
     * @return bool Whether the operation was successful
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
            error_log('Error while removing from favorites: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user's favorite recipes
     *
     * @param \PDO $pdo
     * @param int $userId The user identifier
     * @return array List of favorite recipes
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
            error_log('Error while querying favorite recipes: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Checks if a recipe is a user's favorite
     *
     * @param \PDO $pdo
     * @param int $userId The user identifier
     * @param int $recipeId The recipe identifier
     * @return bool Whether the recipe is a favorite
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
            error_log('Error while checking favorite status: ' . $e->getMessage());
            return false;
        }
    }
}
