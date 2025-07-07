<?php

declare(strict_types=1);

namespace WebDevProject\Controller;

use Gemini;
use PDO;
use WebDevProject\Core\Auth;
use WebDevProject\Model\Recipe;

class RecipeController
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * GET /recipes - Listing and filtering recipes
     */
    public function index(): void
    {
        $filters = [
            'prep_time' => isset($_GET['prep_time']) && is_numeric($_GET['prep_time']) ? (int)$_GET['prep_time'] : null,
            'cook_time' => isset($_GET['cook_time']) && is_numeric($_GET['cook_time']) ? (int)$_GET['cook_time'] : null,
            'category' => isset($_GET['category']) ? trim($_GET['category']) : null
        ];

        // Load categories from database
        $categories = \WebDevProject\Model\Recipe::getAllCategories($this->pdo);

        // Pagination parameters
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 6; // 6 recipes per page - configurable value

        // Make sure pagination info will be visible to the user in the view

        try {
            // Load recipes and total count
            $result = $this->getFilteredRecipes($filters, $page, $perPage);
            $recipes = $result['recipes'];
            $totalRecipes = $result['total'];

            // Calculate pagination data
            $totalPages = ceil($totalRecipes / $perPage);

            // Logged in user ID for checking favorites
            $userId = (int)($_SESSION['user_id'] ?? 0);

            // Transform image paths and other fields for the view
            foreach ($recipes as &$recipe) {
                $recipe['name'] = $recipe['title'];
                $recipe['image'] = $recipe['image_path'] ?? '/assets/slide' . (($recipe['id'] % 3) + 1) . '.png';

                // Create a shortened version of the description
                if (mb_strlen($recipe['description']) > 100) {
                    $recipe['short_description'] = mb_substr($recipe['description'], 0, 100) . '...';
                } else {
                    $recipe['short_description'] = $recipe['description'];
                }

                // Check if the recipe is among favorites
                $recipe['is_favorite'] = false;
                if ($userId > 0) {
                    $recipe['is_favorite'] = Recipe::isFavorite($this->pdo, $userId, $recipe['id']);
                }
            }
        } catch (\Exception $e) {
            // In case of error, use the default demo recipes
            error_log('Error loading recipes: ' . $e->getMessage());
        }

        // We also pass the filter parameters to the view to set the form fields
        $this->render(compact(
            'recipes',
            'filters',
            'page',
            'perPage',
            'totalPages',
            'totalRecipes',
            'categories'
        ));
    }

    /**
     * Filter recipes based on given conditions with pagination
     *
     * @param array $filters The filter conditions
     * @param int $page The current page number
     * @param int $perPage The number of items per page
     * @return array The results array: ['recipes' => array, 'total' => int]
     */
    private function getFilteredRecipes(array $filters, int $page = 1, int $perPage = 6): array
    {
        $where = [];
        $params = [];

        // Constructing WHERE conditions
        $whereClause = "WHERE r.verified_at IS NOT NULL";

        // Preparation time filter
        if (!empty($filters['prep_time'])) {
            $where[] = "r.prep_time <= ?";
            $params[] = $filters['prep_time'];
        }

        // Cooking time filter
        if (!empty($filters['cook_time'])) {
            $where[] = "r.cook_time <= ?";
            $params[] = $filters['cook_time'];
        }

        // Category filter
        if (!empty($filters['category'])) {
            $where[] = "c.name = ?";
            $params[] = $filters['category'];
        }

        // Adding WHERE conditions to the query
        if (!empty($where)) {
            $whereClause .= " AND " . implode(" AND ", $where);
        }

        // 1. First query the total count for pagination
        $countSql = "SELECT COUNT(r.id) as total
                    FROM recipes r 
                    LEFT JOIN categories c ON r.category_id = c.id 
                    $whereClause";

        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalCount = (int)$countStmt->fetchColumn();

        // 2. Query the recipes for the current page
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT r.*, c.name as category, u.username as created_by, rp.path as image_path 
                FROM recipes r 
                LEFT JOIN categories c ON r.category_id = c.id 
                LEFT JOIN users u ON r.user_id = u.id 
                LEFT JOIN recipe_picture rp ON r.id = rp.recipe_id 
                $whereClause
                ORDER BY r.created_at DESC
                LIMIT $perPage OFFSET $offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $recipes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Load ingredients for each recipe
        foreach ($recipes as &$recipe) {
            $recipe['ingredients'] = \WebDevProject\Model\Recipe::getIngredients($this->pdo, $recipe['id']);
        }

        return [
            'recipes' => $recipes,
            'total' => $totalCount
        ];
    }

    /**
     * GET /recipes/{id} - Detailed view of a recipe
     * @param string $id
     */
    public function view(string $id): void
    {
        try {
            $recipeId = (int)$id;
            $recipe = \WebDevProject\Model\Recipe::getById($this->pdo, $recipeId);

            if (!$recipe) {
                http_response_code(404);
                $_SESSION['flash_error'] = 'The requested recipe could not be found.';
                header('Location: /recipes');
                exit;
            }

            // Transform fields for the view
            $recipe['name'] = $recipe['title'];
            $recipe['image'] = $recipe['image_path'] ?? '/assets/slide' . (($recipe['id'] % 3) + 1) . '.png';

            // Transform ingredients for the view
            foreach ($recipe['ingredients'] as &$ingredient) {
                $ingredient['name'] = $ingredient['ingredient_name'];
                $ingredient['unit'] = $ingredient['unit_abbr'];
            }

            // Check if the recipe is among favorites
            $userId = (int)($_SESSION['user_id'] ?? 0);
            $recipe['is_favorite'] = false;

            if ($userId > 0) {
                $recipe['is_favorite'] = Recipe::isFavorite($this->pdo, $userId, $recipeId);
            }
        } catch (\Exception $e) {
            error_log('Error loading recipe: ' . $e->getMessage());
        }

        $this->render(compact('recipe'), 'recipe');
    }

    /**
     * GET /recipe/submit - Submit a new recipe
     */
    public function submitForm(): void
    {
        // Only logged in users can submit recipes
        Auth::requireLogin();

        try {
            // Load categories
            $categories = Recipe::getAllCategories($this->pdo);

            // If there are no categories, load the defaults
            if (empty($categories)) {
                $categories = Recipe::getAllCategories($this->pdo);
            }
        } catch (\Exception $e) {
            error_log('Error loading categories: ' . $e->getMessage());
            $categories = [];
        }

        $this->render(compact('categories'), 'recipe_submit');
    }

    /**
     * POST /recipe/submit - Process recipe submission form
     * @return void
     */
    public function submitProcess(): void
    {
        // Only logged in users can submit recipes
        Auth::requireLogin();

        // CSRF verification with standardized error handling
        \WebDevProject\Helper\CsrfHelper::validate(
            $_POST['csrf'] ?? null,
            '/recipe/submit'
        );

        $userId = (int)($_SESSION['user_id'] ?? 0);

        // Validation
        if (empty($_POST['name']) || empty($_POST['description']) || empty($_POST['instructions'])) {
            $_SESSION['flash_error'] = 'All required fields must be filled in.';
            header('Location: /recipe/submit');
            exit;
        }

        // Collect recipe data
        $recipeData = [
            'user_id'      => $userId,
            'title'        => $_POST['name'],
            'description'  => $_POST['description'],
            'instructions' => $_POST['instructions'],
            'prep_time'    => isset($_POST['prep_time']) ? (int)$_POST['prep_time'] : null,
            'cook_time'    => isset($_POST['cook_time']) ? (int)$_POST['cook_time'] : null,
            'servings'     => isset($_POST['servings']) ? (int)$_POST['servings'] : null
        ];

        // Category handling
        if (!empty($_POST['category'])) {
            $recipeData['category_id'] = \WebDevProject\Model\Recipe::getCategoryId($this->pdo, $_POST['category']);
        }

        try {
            // Save recipe
            $recipeId = \WebDevProject\Model\Recipe::create($this->pdo, $recipeData);

            // Process and save ingredients
            if (isset($_POST['ingredients']) && is_array($_POST['ingredients'])) {
                $ingredients = [];

                foreach ($_POST['ingredients'] as $ingredient) {
                    if (empty($ingredient['ingredient_id']) || empty($ingredient['quantity'])) {
                        continue; // Skip invalid ingredients
                    }

                    $ingredients[] = [
                        'ingredient_id' => (int)$ingredient['ingredient_id'],
                        'quantity'      => (float)$ingredient['quantity']
                    ];
                }

                \WebDevProject\Model\Recipe::saveIngredients($this->pdo, $recipeId, $ingredients);
            }

            // Handle image upload, if any
            if (!empty($_FILES['image']['name'])) {
                try {
                    $this->handleImageUpload($recipeId);
                    // Continue, because the recipe can be created without an image
                } catch (\Exception $uploadEx) {
                    // Continue silently, because the recipe can be created without an image
                }
            }

            $_SESSION['flash'] = 'Recipe submitted successfully!';
            header('Location: /recipes');
        } catch (\Exception $e) {
            error_log('Error saving recipe: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'An error occurred while saving the recipe. Please try again later.';
            header('Location: /recipe/submit');
        }

        exit;
    }

    /**
     * Image upload handling - Minimal version
     *
     * @param int $recipeId The recipe ID
     * @return bool Whether the upload was successful
     */
    private function handleImageUpload(int $recipeId): bool
    {
        try {
            // Check if there's a file to upload and there's no error
            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                return false;
            }

            // Prepare directory
            $upload_dir = __DIR__ . '/../../public_html/uploads/recipes/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Generate filename and upload
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $new_file_name = $recipeId . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Set permissions
                chmod($upload_path, 0644);

                // Save path to database
                $image_path = '/uploads/recipes/' . $new_file_name;

                // Check if there's already an image for this recipe
                $checkStmt = $this->pdo->prepare("SELECT id FROM recipe_picture WHERE recipe_id = :recipe_id");
                $checkStmt->execute(['recipe_id' => $recipeId]);

                if ($checkStmt->fetch()) {
                    // Update if exists
                    $stmt = $this->pdo->
                    prepare("UPDATE recipe_picture SET path = :path WHERE recipe_id = :recipe_id");
                } else {
                    // Insert new image
                    $stmt = $this->pdo->
                    prepare("INSERT INTO recipe_picture (recipe_id, path) VALUES (:recipe_id, :path)");
                }

                $stmt->execute([
                    'recipe_id' => $recipeId,
                    'path' => $image_path
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            // No logging, just a simple return value
            $_SESSION['flash_error'] = 'Error uploading image.';
            return false;
        }
    }

    /**
     * GET /recipes/recommend - Recommending recipes based on fridge contents
     */
    public function recommend(): void
    {
        // Only logged-in users can get recommendations
        Auth::requireLogin();

        $userId = (int)($_SESSION['user_id'] ?? 0);
        $recommendedRecipes = [];
        $missingIngredientsCount = [];

        try {
            // 1. Query the contents of the fridge
            $fridgeItems = $this->getUserFridgeItems($userId);
            if (empty($fridgeItems)) {
                $_SESSION['flash_error'] = 'Your fridge is empty, add some ingredients so we can recommend recipes!';
                header('Location: /fridge');
                exit;
            }

            // 2. Query all recipes with ingredients
            $recipes = Recipe::getAllWithIngredients($this->pdo);

            // 3. Filter recipes based on fridge contents
            foreach ($recipes as $recipe) {
                $missingCount = $this->countMissingIngredients($recipe['ingredients'], $fridgeItems);

                // Only keep recipes that are missing at most 2 ingredients
                if ($missingCount <= 2) {
                    // Transform fields for the view before adding to array
                    $recipe['name'] = $recipe['title'];
                    $recipe['image'] = $recipe['image_path'] ?? '/assets/slide' . (($recipe['id'] % 3) + 1) . '.png';

                    // Create a shortened version of the description
                    if (mb_strlen($recipe['description']) > 100) {
                        $recipe['short_description'] = mb_substr($recipe['description'], 0, 100) . '...';
                    } else {
                        $recipe['short_description'] = $recipe['description'];
                    }

                    $recommendedRecipes[] = $recipe;
                    $missingIngredientsCount[$recipe['id']] = $missingCount;
                }
            }

            // 4. Sort by the number of missing ingredients (recipes with the fewest missing ingredients first)
            usort($recommendedRecipes, function ($a, $b) use ($missingIngredientsCount) {
                return $missingIngredientsCount[$a['id']] - $missingIngredientsCount[$b['id']];
            });
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'An error occurred while loading recipe recommendations.';
            header('Location: /recipes');
            exit;
        }

        // 5. Display the recommended recipes
        $this->render([
            'recipes' => $recommendedRecipes,
            'missingCounts' => $missingIngredientsCount,
            'isRecommendation' => true
        ], 'recipe_recommendations');
    }

    /**
     * Query the ingredients in the user's fridge
     *
     * @param int $userId The user ID
     * @return array List of ingredients [ingredient_id => [quantity, unit_abbr, unit_name]]
     */
    private function getUserFridgeItems(int $userId): array
    {
        $items = [];

        // Use the existing FridgeItem model
        $fridgeItems = \WebDevProject\Model\FridgeItem::getByUser($this->pdo, $userId);

        foreach ($fridgeItems as $item) {
            if (isset($item['ingredient_id']) && isset($item['quantity'])) {
                $items[$item['ingredient_id']] = [
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit_abbr'] ?? '',
                    'unit_name' => $item['unit_name'] ?? ''
                ];
            }
        }

        return $items;
    }

    /**
     * Count how many ingredients are missing from the fridge for a recipe
     *
     * @param array $recipeIngredients The recipe ingredients
     * @param array $fridgeItems The fridge contents
     * @return int The number of missing ingredients
     */
    private function countMissingIngredients(array $recipeIngredients, array $fridgeItems): int
    {
        $missingCount = 0;

        foreach ($recipeIngredients as $ingredient) {
            // Check if the required keys exist
            if (!isset($ingredient['ingredient_id']) || !isset($ingredient['quantity'])) {
                continue;
            }

            $ingredientId = $ingredient['ingredient_id'];
            $requiredQuantity = $ingredient['quantity'];

            // If it's not in the fridge, or there's less than needed
            if (!isset($fridgeItems[$ingredientId]) || $fridgeItems[$ingredientId]['quantity'] < $requiredQuantity) {
                $missingCount++;
            }
        }

        return $missingCount;
    }

    /**
     * GET /recipes/recommend/ai - Request AI-based recipe recommendation
     */
    public function aiRecommend(): void
    {
        // Only logged-in users can get recommendations
        Auth::requireLogin();

        $userId = (int)($_SESSION['user_id'] ?? 0);

        try {
            // 1. Query the fridge contents with units
            $fridgeItems = $this->getUserFridgeItems($userId);
            if (empty($fridgeItems)) {
                $_SESSION['flash_error'] = 'Your fridge is empty, add some ingredients so we can recommend recipes!';
                header('Location: /fridge');
                exit;
            }

            // 2. Get ingredient names and units
            $ingredientData = $this->getIngredientNames(array_keys($fridgeItems));

            // 3. Combine unit information with ingredients
            $completeIngredientData = [];
            foreach ($ingredientData as $ingredientId => $data) {
                if (isset($fridgeItems[$ingredientId])) {
                    $completeIngredientData[$ingredientId] = [
                        'name' => $data['name'],
                        'quantity' => $fridgeItems[$ingredientId]['quantity'],
                        'unit' => $fridgeItems[$ingredientId]['unit']
                    ];
                } else {
                    $completeIngredientData[$ingredientId] = $data['name'];
                }
            }

            // 4. Prepare Gemini API request
            $apiKey = $_ENV['GOOGLE_GEMINI'] ?? '';
            if (empty($apiKey)) {
                $_SESSION['flash_error'] = 'The AI service is currently unavailable. Please try again later!';
                header('Location: /recipes/recommend');
                exit;
            }

            // 5. Request recipe recommendation from Gemini API with complete ingredient data
            $recommendedRecipe = $this->getAIRecommendation($apiKey, $completeIngredientData);

            // 6. Display the results
            // Extract only the names from the complex data structure
            $ingredientNames = [];
            foreach ($completeIngredientData as $id => $data) {
                if (is_array($data) && isset($data['name'])) {
                    $ingredientNames[] = $data['name'];
                } elseif (is_string($data)) {
                    $ingredientNames[] = $data;
                }
            }

            $this->render([
                'aiRecommendation' => $recommendedRecipe,
                'ingredients' => $ingredientNames
            ], 'recipe_ai_recommendation');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] =
                'An error occurred while loading the AI recipe recommendation: ' . $e->getMessage();
            header('Location: /recipes/recommend');
            exit;
        }
    }

    /**
     * Query ingredient names and data based on IDs
     *
     * @param array $ingredientIds The ingredient IDs
     * @return array Ingredient data [id => [name, unit, quantity]]
     */
    private function getIngredientNames(array $ingredientIds): array
    {
        if (empty($ingredientIds)) {
            return [];
        }

        $inClause = implode(',', array_fill(0, count($ingredientIds), '?'));

        $stmt = $this->pdo->prepare("
            SELECT i.id, i.name, u.abbreviation as unit_abbr, u.name as unit_name
            FROM ingredients i
            LEFT JOIN units u ON i.unit_id = u.id
            WHERE i.id IN ($inClause)
        ");

        // Provide parameters for the IN clause
        foreach ($ingredientIds as $index => $id) {
            $stmt->bindValue($index + 1, $id, \PDO::PARAM_INT);
        }

        $stmt->execute();
        $ingredients = [];

        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $ingredient) {
            $ingredients[$ingredient['id']] = [
                'name' => $ingredient['name'],
                'unit' => $ingredient['unit_abbr'] ?? '',
                'unit_name' => $ingredient['unit_name'] ?? ''
            ];
        }

        return $ingredients;
    }

    /**
     * Request recipe recommendation from the Gemini API
     *
     * @param string $apiKey The Gemini API key
     * @param array $ingredients The ingredient names
     * @return array The recommended recipe data
     */
    private function getAIRecommendation(string $apiKey, array $ingredients): array
    {
        // New formatting: collecting name and unit information in an organized way for the prompt
        $ingredientsForPrompt = [];
        $unitPreferences = [];

        foreach ($ingredients as $id => $data) {
            if (is_array($data) && isset($data['name'], $data['unit'])) {
                $ingredientsForPrompt[] = $data['name'];
                $unitPreferences[$data['name']] = $data['unit'];
            } else {
                $ingredientsForPrompt[] = $data;
            }
        }

        $ingredientsText = implode(', ', $ingredientsForPrompt);
        $unitPreferencesJSON = json_encode($unitPreferences, JSON_UNESCAPED_UNICODE);

        // Create Gemini PHP client
        $client = Gemini::client($apiKey);

        // Define generative model
        $model = $client->generativeModel(model: 'gemini-1.5-flash');

        // Prepare unit preferences in text format
        $unitPreferencesText = '';
        if (!empty($unitPreferences)) {
            $unitPrefItems = [];
            foreach ($unitPreferences as $ingredient => $unit) {
                $unitPrefItems[] = "$ingredient: $unit";
            }
            $unitPreferencesText = implode(', ', $unitPrefItems);
        } else {
            $unitPreferencesText = "no specified preference";
        }

        // Kérés összeállítása - kibővítve a mértékegység preferenciákkal és nem kell minden alapanyagot használni
        $prompt = "Ajánlj egy receptet, amit elkészíthetek a következő alapanyagok NÉHÁNYÁBÓL (nem kell az összeset
         felhasználnod): $ingredientsText. 
        Válassz ki 3-6 alapanyagot a listából, amelyek jól illenek egymáshoz, és azokból készíts egy receptet.
        
        A válaszodban csak a következő mezőket add meg, JSON formátumban:
        {
            \"title\": \"A recept címe\",
            \"ingredients\": [
                {\"name\": \"hozzávaló1\", \"quantity\": \"200\", \"unit\": \"g\"},
                {\"name\": \"hozzávaló2\", \"quantity\": \"2\", \"unit\": \"db\"},
                ...
            ],
            \"instructions\": \"Elkészítési útmutató lépésekre bontva\",
            \"preparationTime\": \"Elkészítési idő percben\",
            \"cookTime\": \"Főzési idő percben\",
            \"description\": \"Rövid leírás a receptről, megemlítve mely fő alapanyagokat használtál fel\",
            \"servings\": \"4\"
        }
        
        Fontos: A fűszereket (só, bors, pirospaprika, stb.) NE add a hozzávalók listájához, azok csak az 
        elkészítési útmutatóban legyenek megemlítve. 
        Az ingredients listában csak a fő alapanyagok szerepeljenek, mindegyiknél a pontos mennyiséget és 
        mértékegységet (g, dkg, ml, dl, db, ek, tk, csipet, stb.) is add meg.
        
        A felhasználó a következő mértékegységekben tárolja az alapanyagokat: $unitPreferencesText
        Amikor csak lehetséges, használd ezeket a mértékegységeket a hozzávalók mennyiségének megadásához!
        
        A servings mezőben add meg, hogy hány adagra elég a recept.
        Ne adj meg semmilyen más szöveget, csak a JSON választ.";

        try {
            // Generate content
            $result = $model->generateContent($prompt);

            // Extract text from response
            $textResponse = $result->text();

            // Extract JSON data from the response
            $jsonStart = strpos($textResponse, '{');
            $jsonEnd = strrpos($textResponse, '}');

            if ($jsonStart !== false && $jsonEnd !== false) {
                $jsonString = substr($textResponse, $jsonStart, $jsonEnd - $jsonStart + 1);
                $recipeData = json_decode($jsonString, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    return $recipeData;
                }
            }

            // Fallback: if JSON processing fails
            // Convert ingredients to structured format
            $formattedIngredients = [];
            foreach ($ingredientsForPrompt as $index => $name) {
                $formattedIngredients[] = [
                    'name' => $name,
                    'quantity' => '1',
                    'unit' => 'db'
                ];
            }

            return [
                'title' => 'Recommended Recipe',
                'ingredients' => $formattedIngredients,
                'instructions' => $textResponse,
                'preparationTime' => '30',
                'cookTime' => '20',
                'description' => 'Automatically generated recipe from the available ingredients.'
            ];
        } catch (\Exception $e) {
            // In case of error, provide simplified recipe recommendation
            $formattedIngredients = [];
            foreach ($ingredientsForPrompt as $index => $name) {
                $formattedIngredients[] = [
                    'name' => $name,
                    'quantity' => '1',
                    'unit' => 'pcs'
                ];
            }

            return [
                'title' => 'Simple Recommended Recipe',
                'ingredients' => $formattedIngredients,
                'instructions' =>
                    'Unfortunately, we couldn\'t generate a recipe recommendation right now. Please try again later.',
                'preparationTime' => 'unknown',
                'cookTime' => 'unknown',
                'description' => 'Recipe recommendation is currently unavailable.'
            ];
        }
    }

    /**
     * POST /recipes/save-ai-recipe - Save AI generated recipe to the database
     */
    public function saveAiRecipe(): void
    {
        // Only logged-in users can save recipes
        Auth::requireLogin();

        // CSRF verification with standardized error handling
        \WebDevProject\Helper\CsrfHelper::validate(
            $_POST['csrf'] ?? null,
            '/recipes/recommend/ai',
            'Invalid CSRF token. Please try again.'
        );

        $userId = (int)($_SESSION['user_id'] ?? 0);

        // Check recipe data
        if (empty($_POST['recipe_data']) || empty($_POST['recipe_title']) || empty($_POST['recipe_category'])) {
            $_SESSION['flash_error'] = 'Missing data for saving the recipe.';
            header('Location: /recipes/recommend/ai');
            exit;
        }

        try {
            // Decode JSON data
            $recipeData = json_decode($_POST['recipe_data'], true);
            if (json_last_error() !== JSON_ERROR_NONE || empty($recipeData)) {
                throw new \Exception('Invalid recipe data.');
            }

            // Get or create recipe category ID
            $categoryId = Recipe::getCategoryId($this->pdo, $_POST['recipe_category']);

            // Prepare recipe data
            $newRecipe = [
                'user_id'      => $userId,
                'title'        => $_POST['recipe_title'],
                'description'  => $recipeData['description'] ?? '',
                'instructions' => $recipeData['instructions'] ?? '',
                'prep_time'    => !empty($recipeData['preparationTime']) && is_numeric($recipeData['preparationTime']) ?
                                 (int)$recipeData['preparationTime'] : null,
                'cook_time'    => !empty($recipeData['cookTime']) && is_numeric($recipeData['cookTime']) ?
                                (int)$recipeData['cookTime'] : null,
                'servings'     => !empty($recipeData['servings']) && is_numeric($recipeData['servings']) ?
                                (int)$recipeData['servings'] : 4,
                'category_id'  => $categoryId
            ];

            // Create recipe in database
            $recipeId = Recipe::create($this->pdo, $newRecipe);

            // Process and save ingredients
            if (!empty($recipeData['ingredients']) && is_array($recipeData['ingredients'])) {
                $ingredients = [];

                foreach ($recipeData['ingredients'] as $ingredient) {
                    // Check if ingredient is in new format (as array)
                    if (is_array($ingredient) && isset($ingredient['name'])) {
                        $ingredientName = $ingredient['name'];
                        $quantity = $this->parseQuantity($ingredient['quantity'] ?? '1');

                        // Find or create ingredients (name and unit)
                        $ingredientId = $this->findOrCreateIngredient($ingredientName, $ingredient['unit'] ?? null);
                    } else {
                        // Support old format (string only)
                        $ingredientName = $ingredient;
                        $quantity = 1.0;
                        $ingredientId = $this->findOrCreateIngredient($ingredientName);
                    }

                    if ($ingredientId) {
                        $ingredients[] = [
                            'ingredient_id' => $ingredientId,
                            'quantity'      => $quantity
                        ];
                    }
                }

                if (!empty($ingredients)) {
                    Recipe::saveIngredients($this->pdo, $recipeId, $ingredients);
                }
            }

            $_SESSION['flash'] = 'AI-generated recipe successfully saved!';
            header('Location: /recipe/' . $recipeId);
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error occurred while saving the recipe: ' . $e->getMessage();
            header('Location: /recipes/recommend/ai');
        }

        exit;
    }

    /**
     * Find or create ingredient based on name and unit
     *
     * @param string $name The ingredient name
     * @param string|null $unit The unit of measurement, if available
     * @return int|null The ingredient ID or null in case of error
     */
    private function findOrCreateIngredient(string $name, ?string $unit = null): ?int
    {
        $name = trim($name);
        if (empty($name)) {
            return null;
        }

        try {
            // Search for existing ingredient
            $stmt = $this->pdo->prepare("SELECT id FROM ingredients WHERE name = :name LIMIT 1");
            $stmt->execute(['name' => $name]);

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result) {
                // If unit is provided and this is an existing ingredient, update the unit if not already set
                if (!empty($unit)) {
                    $unitId = $this->getOrCreateUnit($unit);

                    // Check if the ingredient already has a unit
                    $checkUnit = $this->pdo->prepare("SELECT unit_id FROM ingredients WHERE id = :id");
                    $checkUnit->execute(['id' => $result['id']]);
                    $currentUnit = $checkUnit->fetch(\PDO::FETCH_ASSOC);

                    // If no unit is set, set it now
                    if (!$currentUnit || empty($currentUnit['unit_id'])) {
                        $updateStmt = $this->pdo->prepare("UPDATE ingredients SET unit_id = :unit_id WHERE id = :id");
                        $updateStmt->execute([
                            'unit_id' => $unitId,
                            'id' => $result['id']
                        ]);
                    }
                }

                return (int)$result['id'];
            }

            // If not found, create a new one
            $unitId = !empty($unit) ? $this->getOrCreateUnit($unit) : null;

            if ($unitId) {
                $stmt = $this->pdo->prepare("INSERT INTO ingredients (name, unit_id) VALUES (:name, :unit_id)");
                $stmt->execute([
                    'name' => $name,
                    'unit_id' => $unitId
                ]);
            } else {
                $stmt = $this->pdo->prepare("INSERT INTO ingredients (name) VALUES (:name)");
                $stmt->execute(['name' => $name]);
            }

            return (int)$this->pdo->lastInsertId();
        } catch (\Exception $e) {
            error_log('Error while searching for/creating ingredient: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Find or create unit by name
     *
     * @param string $unitName The unit name or abbreviation
     * @return int|null The unit ID or null in case of error
     */
    private function getOrCreateUnit(string $unitName): ?int
    {
        $unitName = trim($unitName);
        if (empty($unitName)) {
            return null;
        }

        try {
            // Normalize the unit name
            $normalizedUnitName = $this->normalizeUnitName($unitName);

            // Search for an existing unit by name or abbreviation
            $stmt = $this->pdo->prepare("
                SELECT id FROM units 
                WHERE name = :name 
                OR abbreviation = :abbr
                LIMIT 1
            ");
            $stmt->execute([
                'name' => $normalizedUnitName,
                'abbr' => $unitName
            ]);

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result) {
                return (int)$result['id'];
            }

            // If not found, create a new one
            $fullName = $this->getFullUnitName($normalizedUnitName);
            $abbr = strlen($unitName) <= 5 ? $unitName : substr($normalizedUnitName, 0, 5);

            $stmt = $this->pdo->prepare("
                INSERT INTO units (name, abbreviation) 
                VALUES (:name, :abbr)
            ");
            $stmt->execute([
                'name' => $fullName,
                'abbr' => $abbr
            ]);

            return (int)$this->pdo->lastInsertId();
        } catch (\Exception $e) {
            error_log('Error while searching for/creating unit: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Normalize unit name (abbreviation -> full name)
     *
     * @param string $unitName The unit name or abbreviation
     * @return string The normalized unit name
     */
    private function normalizeUnitName(string $unitName): string
    {
        $unitMap = [
            'g' => 'gram',
            'dkg' => 'decagram',
            'kg' => 'kilogram',
            'ml' => 'milliliter',
            'dl' => 'deciliter',
            'l' => 'liter',
            'db' => 'piece',
            'cs' => 'package',
            'ek' => 'tablespoon',
            'tk' => 'teaspoon',
            'csp' => 'pinch',
            'fej' => 'head',
            'gerezd' => 'clove',
            'szelet' => 'slice',
            'csésze' => 'cup',
            'bögre' => 'mug',
            'marék' => 'handful',
            'közepes' => 'medium',
            'nagy' => 'large',
            'kicsi' => 'small'
        ];

        $unitName = strtolower($unitName);
        return $unitMap[$unitName] ?? $unitName;
    }

    /**
     * Determine full unit name
     *
     * @param string $unitName The unit name
     * @return string The full unit name
     */
    private function getFullUnitName(string $unitName): string
    {
        // If the unit is already a full name, just return it
        if (strlen($unitName) > 5) {
            return $unitName;
        }

        // Otherwise, check if it's a known abbreviation
        return $this->normalizeUnitName($unitName);
    }

    /**
     * Parse quantity from text format
     *
     * @param string $quantityStr The quantity in text format
     * @return float The processed quantity as a number
     */
    private function parseQuantity(string $quantityStr): float
    {
        // Remove non-numeric characters, except decimal point/comma
        $quantityStr = trim($quantityStr);

        // If empty, default to 1.0
        if (empty($quantityStr)) {
            return 1.0;
        }

        // Handle fractions (e.g., "1/2")
        if (strpos($quantityStr, '/') !== false) {
            $parts = explode('/', $quantityStr);
            if (count($parts) == 2 && is_numeric($parts[0]) && is_numeric($parts[1]) && $parts[1] != 0) {
                return (float)$parts[0] / (float)$parts[1];
            }
        }

        // In other cases, try to interpret as a number
        // Replace comma with dot for decimal point
        $quantityStr = str_replace(',', '.', $quantityStr);

        // Keep only digits and decimal point
        $quantityStr = preg_replace('/[^0-9.]/', '', $quantityStr);

        if (is_numeric($quantityStr)) {
            return (float)$quantityStr;
        }

        // If processing failed, default to 1.0
        return 1.0;
    }

    /**
     * Simple view render helper
     */
    private function render(array $vars = [], string $view = 'recipes'): void
    {
        extract($vars, EXTR_SKIP);
        ob_start();
        include __DIR__ . "/../View/pages/{$view}.php";
        $content = ob_get_clean();

        include __DIR__ . '/../View/layout.php';
    }
}
