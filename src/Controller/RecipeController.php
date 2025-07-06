<?php

namespace WebDevProject\Controller;

use PDO;
use WebDevProject\Core\Auth;
use WebDevProject\Config\Config;
use WebDevProject\Model\Recipe;
use Gemini\Enums\ModelVariation;
use Gemini\GeminiHelper;
use Gemini;

class RecipeController
{
    public function __construct(
        private PDO $pdo
    ) {
        // Nem szükséges bejelentkezés a receptek megtekintéséhez
    }

    /**
     * GET /recipes - Receptek listázása és szűrése
     */
    public function index(): void
    {
        // Szűrési paraméterek begyűjtése
        $filters = [
            'prep_time' => isset($_GET['prep_time']) && is_numeric($_GET['prep_time']) ? (int)$_GET['prep_time'] : null,
            'cook_time' => isset($_GET['cook_time']) && is_numeric($_GET['cook_time']) ? (int)$_GET['cook_time'] : null,
            'category' => isset($_GET['category']) ? trim($_GET['category']) : null
        ];

        // Kategóriák betöltése az adatbázisból
        $categories = \WebDevProject\Model\Recipe::getAllCategories($this->pdo);

        // Lapozási paraméterek
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 20; // 20 recept oldalanként

        try {
            // Receptek és összes találat számának betöltése
            $result = $this->getFilteredRecipes($filters, $page, $perPage);
            $recipes = $result['recipes'];
            $totalRecipes = $result['total'];

            // Lapozási adatok számítása
            $totalPages = ceil($totalRecipes / $perPage);

            // Bejelentkezett felhasználó azonosítója a kedvencek ellenőrzéséhez
            $userId = (int)($_SESSION['user_id'] ?? 0);

            // Képek elérési útját és egyéb mezőket alakítsunk át a view-nak megfelelően
            foreach ($recipes as &$recipe) {
                $recipe['name'] = $recipe['title'];
                $recipe['image'] = $recipe['image_path'] ?? '/assets/slide' . (($recipe['id'] % 3) + 1) . '.png';

                // Létrehoz egy rövidített változatot a leírásból
                if (mb_strlen($recipe['description']) > 100) {
                    $recipe['short_description'] = mb_substr($recipe['description'], 0, 100) . '...';
                } else {
                    $recipe['short_description'] = $recipe['description'];
                }

                // Ellenőrizzük, hogy a recept a kedvencek között van-e
                $recipe['is_favorite'] = false;
                if ($userId > 0) {
                    $recipe['is_favorite'] = Recipe::isFavorite($this->pdo, $userId, $recipe['id']);
                }
            }
        } catch (\Exception $e) {
            // Hiba esetén használjuk az alapértelmezett demo recepteket
            error_log('Hiba a receptek betöltésekor: ' . $e->getMessage());

            $recipes = [
                [
                    'id' => 1,
                    'name' => 'Paprikás Krumpli',
                    'category' => 'Főétel',
                    'image' => '/assets/slide1.png',
                    'created_by' => 'szakacs01',
                    'description' => 'Klasszikus magyar étel, ami egyszerűen és gyorsan elkészíthető.',
                    'prep_time' => 15,
                    'cook_time' => 30,
                    'is_favorite' => false
                ],
                [
                    'id' => 2,
                    'name' => 'Túrós Csusza',
                    'category' => 'Főétel',
                    'image' => '/assets/slide2.png',
                    'created_by' => 'gasztro_guru',
                    'description' => 'Tejfölös-túrós tészta szalonnával, igazi magyaros étel.',
                    'prep_time' => 10,
                    'cook_time' => 20,
                    'is_favorite' => false
                ],
                [
                    'id' => 3,
                    'name' => 'Gyümölcssaláta',
                    'category' => 'Desszert',
                    'image' => '/assets/slide3.png',
                    'created_by' => 'vitamin_lover',
                    'description' => 'Frissítő, vitamindús gyümölcssaláta, tökéletes a nyári napokra.',
                    'prep_time' => 15,
                    'cook_time' => 0,
                    'is_favorite' => false
                ]
            ];
        }

        // A szűrési paramétereket is átadjuk a view-nak, hogy beállítsa a form mezőket
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
     * Receptek szűrése a megadott feltételek alapján lapozással
     *
     * @param array $filters A szűrési feltételek
     * @param int $page Az aktuális oldal száma
     * @param int $perPage Az oldalankénti elemszám
     * @return array Az eredmények tömbje: ['recipes' => array, 'total' => int]
     */
    private function getFilteredRecipes(array $filters, int $page = 1, int $perPage = 20): array
    {
        $where = [];
        $params = [];

        // WHERE feltételek összeállítása
        $whereClause = "WHERE r.verified_at IS NOT NULL";

        // Elkészítési idő szűrő
        if (!empty($filters['prep_time'])) {
            $where[] = "r.prep_time <= ?";
            $params[] = $filters['prep_time'];
        }

        // Főzési idő szűrő
        if (!empty($filters['cook_time'])) {
            $where[] = "r.cook_time <= ?";
            $params[] = $filters['cook_time'];
        }

        // Kategória szűrő
        if (!empty($filters['category'])) {
            $where[] = "c.name = ?";
            $params[] = $filters['category'];
        }

        // WHERE feltételek hozzáadása a lekérdezéshez
        if (!empty($where)) {
            $whereClause .= " AND " . implode(" AND ", $where);
        }

        // 1. Először lekérdezzük az összes találat számát a lapozáshoz
        $countSql = "SELECT COUNT(r.id) as total
                    FROM recipes r 
                    LEFT JOIN categories c ON r.category_id = c.id 
                    $whereClause";

        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalCount = (int)$countStmt->fetchColumn();

        // 2. Lekérdezzük az aktuális oldalra eső recepteket
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

        // Hozzávalók betöltése minden recepthez
        foreach ($recipes as &$recipe) {
            $recipe['ingredients'] = \WebDevProject\Model\Recipe::getIngredients($this->pdo, $recipe['id']);
        }

        return [
            'recipes' => $recipes,
            'total' => $totalCount
        ];
    }

    /**
     * GET /recipes/{id} - Egy recept részletes nézete
     */
    public function view(string $id): void
    {
        try {
            $recipeId = (int)$id;
            $recipe = \WebDevProject\Model\Recipe::getById($this->pdo, $recipeId);

            if (!$recipe) {
                http_response_code(404);
                $_SESSION['flash_error'] = 'A keresett recept nem található.';
                header('Location: /recipes');
                exit;
            }

            // Mezők átalakítása a view számára
            $recipe['name'] = $recipe['title'];
            $recipe['image'] = $recipe['image_path'] ?? '/assets/slide' . (($recipe['id'] % 3) + 1) . '.png';

            // Hozzávalók átalakítása a view számára
            foreach ($recipe['ingredients'] as &$ingredient) {
                $ingredient['name'] = $ingredient['ingredient_name'];
                $ingredient['unit'] = $ingredient['unit_abbr'];
            }

            // Ellenőrizzük, hogy a recept a kedvencek között van-e
            $userId = (int)($_SESSION['user_id'] ?? 0);
            $recipe['is_favorite'] = false;

            if ($userId > 0) {
                $recipe['is_favorite'] = Recipe::isFavorite($this->pdo, $userId, $recipeId);
            }
        } catch (\Exception $e) {
            error_log('Hiba a recept betöltésekor: ' . $e->getMessage());

            // Hibakezelés esetén alapértelmezett recept
            $recipe = [
                'id' => (int)$id,
                'name' => 'Példa Recept',
                'category' => 'Főétel',
                'image' => '/assets/slide1.png',
                'created_by' => 'szakacs01',
                'created_at' => '2025-07-05',
                'description' => 'Ez egy példa recept. Az adatbázisból való betöltés sikertelen volt.',
                'ingredients' => [
                    ['name' => 'hozzávaló 1', 'quantity' => 100, 'unit' => 'g'],
                    ['name' => 'hozzávaló 2', 'quantity' => 200, 'unit' => 'g'],
                ],
                'instructions' => 'Ez egy példa recept leírás. Az adatbázisból való betöltés sikertelen volt.',
                'is_favorite' => false
            ];
        }

        $this->render(compact('recipe'), 'recipe');
    }

    /**
     * GET /recipe/submit - Új recept beküldése
     */
    public function submitForm(): void
    {
        // Csak bejelentkezett felhasználók küldhetnek be receptet
        Auth::requireLogin();

        try {
            // Kategóriák betöltése
            $categories = Recipe::getAllCategories($this->pdo);

            // Ha nincsenek kategóriák, töltsük be az alapértelmezetteket
            if (empty($categories)) {
                $defaultCategories = ['Előétel', 'Leves', 'Főétel', 'Desszert', 'Saláta', 'Reggeli', 'Egyéb'];

                foreach ($defaultCategories as $category) {
                    Recipe::getCategoryId($this->pdo, $category);
                }

                // Újratöltés
                $categories = Recipe::getAllCategories($this->pdo);
            }
        } catch (\Exception $e) {
            error_log('Hiba a kategóriák betöltésekor: ' . $e->getMessage());
            $categories = [];
        }

        $this->render(compact('categories'), 'recipe_submit');
    }

    /**
     * POST /recipe/submit - Recept beküldési form feldolgozása
     */
    public function submitProcess(): void
    {
        // Csak bejelentkezett felhasználók küldhetnek be receptet
        Auth::requireLogin();

        // CSRF ellenőrzés
        if (!isset($_POST['csrf']) || !\WebDevProject\Security\Csrf::check($_POST['csrf'])) {
            $_SESSION['flash_error'] = 'Érvénytelen CSRF token. Kérjük, próbálja újra.';
            header('Location: /recipe/submit');
            exit;
        }

        $userId = (int)($_SESSION['user_id'] ?? 0);

        // Validálás
        if (empty($_POST['name']) || empty($_POST['description']) || empty($_POST['instructions'])) {
            $_SESSION['flash_error'] = 'Minden kötelező mezőt ki kell tölteni.';
            header('Location: /recipe/submit');
            exit;
        }

        // Recept adatok összegyűjtése
        $recipeData = [
            'user_id'      => $userId,
            'title'        => $_POST['name'],
            'description'  => $_POST['description'],
            'instructions' => $_POST['instructions'],
            'prep_time'    => isset($_POST['prep_time']) ? (int)$_POST['prep_time'] : null,
            'cook_time'    => isset($_POST['cook_time']) ? (int)$_POST['cook_time'] : null,
            'servings'     => isset($_POST['servings']) ? (int)$_POST['servings'] : null
        ];

        // Kategória kezelése
        if (!empty($_POST['category'])) {
            $recipeData['category_id'] = \WebDevProject\Model\Recipe::getCategoryId($this->pdo, $_POST['category']);
        }

        try {
            // Recept mentése
            $recipeId = \WebDevProject\Model\Recipe::create($this->pdo, $recipeData);

            // Hozzávalók feldolgozása és mentése
            if (isset($_POST['ingredients']) && is_array($_POST['ingredients'])) {
                $ingredients = [];

                foreach ($_POST['ingredients'] as $ingredient) {
                    if (empty($ingredient['ingredient_id']) || empty($ingredient['quantity'])) {
                        continue; // Átugorjuk az érvénytelen hozzávalókat
                    }

                    $ingredients[] = [
                        'ingredient_id' => (int)$ingredient['ingredient_id'],
                        'quantity'      => (float)$ingredient['quantity']
                    ];
                }

                \WebDevProject\Model\Recipe::saveIngredients($this->pdo, $recipeId, $ingredients);
            }

            // Kép feltöltés kezelése, ha van
            if (!empty($_FILES['image']['name'])) {
                try {
                    $this->handleImageUpload($recipeId);
                    // Folytatjuk, mert a kép nélkül is létrehozható a recept
                } catch (\Exception $uploadEx) {
                    // Csendben folytatjuk, mert a kép nélkül is létrehozható a recept
                }
            }

            $_SESSION['flash'] = 'Recept sikeresen beküldve!';
            header('Location: /recipes');
        } catch (\Exception $e) {
            error_log('Hiba a recept mentésekor: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Hiba történt a recept mentése közben. Kérjük, próbálja újra később.';
            header('Location: /recipe/submit');
        }

        exit;
    }

    /**
     * Kép feltöltés kezelése - Minimális változat
     *
     * @param int $recipeId A recept azonosítója
     * @return bool Sikeres volt-e a feltöltés
     */
    private function handleImageUpload(int $recipeId): bool
    {
        try {
            // Ellenőrizzük, hogy van-e feltöltendő fájl és nincs-e hiba
            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                return false;
            }

            // Könyvtár előkészítése
            $upload_dir = __DIR__ . '/../../public_html/uploads/recipes/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Fájlnév generálása és feltöltés
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $new_file_name = $recipeId . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Jogosultságok beállítása
                chmod($upload_path, 0644);

                // Elérési út mentése adatbázisba
                $image_path = '/uploads/recipes/' . $new_file_name;

                // Megnézzük, hogy van-e már kép ehhez a recepthez
                $checkStmt = $this->pdo->prepare("SELECT id FROM recipe_picture WHERE recipe_id = :recipe_id");
                $checkStmt->execute(['recipe_id' => $recipeId]);

                if ($checkStmt->fetch()) {
                    // Frissítés, ha már van
                    $stmt = $this->pdo->
                    prepare("UPDATE recipe_picture SET path = :path WHERE recipe_id = :recipe_id");
                } else {
                    // Új kép beszúrása
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
            // Nincs naplózás, csak egy egyszerű visszatérési érték
            $_SESSION['flash_error'] = 'Hiba a kép feltöltése közben.';
            return false;
        }
    }

    /**
     * GET /recipes/recommend - Receptek ajánlása a hűtő tartalma alapján
     */
    public function recommend(): void
    {
        // Csak bejelentkezett felhasználók kaphatnak ajánlást
        Auth::requireLogin();

        $userId = (int)($_SESSION['user_id'] ?? 0);
        $recommendedRecipes = [];
        $missingIngredientsCount = [];

        try {
            // 1. Hűtő tartalmának lekérdezése
            $fridgeItems = $this->getUserFridgeItems($userId);
            if (empty($fridgeItems)) {
                $_SESSION['flash_error'] = 'A hűtőd üres, tölts fel pár alapanyagot, hogy ajánlhassunk recepteket!';
                header('Location: /fridge');
                exit;
            }

            // 2. Az összes recept lekérdezése hozzávalókkal
            $recipes = Recipe::getAllWithIngredients($this->pdo);

            // 3. Receptek szűrése a hűtő tartalma alapján
            foreach ($recipes as $recipe) {
                $missingCount = $this->countMissingIngredients($recipe['ingredients'], $fridgeItems);

                // Csak azokat a recepteket tartjuk meg, amelyekből max. 2 alapanyag hiányzik
                if ($missingCount <= 2) {
                    // Mezők átalakítása a view számára még a tömbhöz adás előtt
                    $recipe['name'] = $recipe['title'];
                    $recipe['image'] = $recipe['image_path'] ?? '/assets/slide' . (($recipe['id'] % 3) + 1) . '.png';

                    // Létrehoz egy rövidített változatot a leírásból
                    if (mb_strlen($recipe['description']) > 100) {
                        $recipe['short_description'] = mb_substr($recipe['description'], 0, 100) . '...';
                    } else {
                        $recipe['short_description'] = $recipe['description'];
                    }

                    $recommendedRecipes[] = $recipe;
                    $missingIngredientsCount[$recipe['id']] = $missingCount;
                }
            }

            // 4. Rendezés a hiányzó alapanyagok száma szerint (legkevesebb hiányzó alapanyaggal
            // rendelkező receptek elöl)
            usort($recommendedRecipes, function ($a, $b) use ($missingIngredientsCount) {
                return $missingIngredientsCount[$a['id']] - $missingIngredientsCount[$b['id']];
            });
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Hiba történt a recept ajánlások betöltése közben.';
            header('Location: /recipes');
            exit;
        }

        // 5. Megjelenítjük a javasolt recepteket
        $this->render([
            'recipes' => $recommendedRecipes,
            'missingCounts' => $missingIngredientsCount,
            'isRecommendation' => true
        ], 'recipe_recommendations');
    }

    /**
     * Lekérdezi a felhasználó hűtőjében lévő alapanyagokat
     *
     * @param int $userId A felhasználó azonosítója
     * @return array Az alapanyagok listája [ingredient_id => [quantity, unit_abbr, unit_name]]
     */
    private function getUserFridgeItems(int $userId): array
    {
        $items = [];

        // Használjuk a már meglévő FridgeItem modellt
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
     * Megszámolja, hogy hány alapanyag hiányzik a recepthez a hűtőből
     *
     * @param array $recipeIngredients A recept hozzávalói
     * @param array $fridgeItems A hűtő tartalma
     * @return int A hiányzó alapanyagok száma
     */
    private function countMissingIngredients(array $recipeIngredients, array $fridgeItems): int
    {
        $missingCount = 0;

        foreach ($recipeIngredients as $ingredient) {
            // Ellenőrizzük, hogy a szükséges kulcsok léteznek-e
            if (!isset($ingredient['ingredient_id']) || !isset($ingredient['quantity'])) {
                continue;
            }

            $ingredientId = $ingredient['ingredient_id'];
            $requiredQuantity = $ingredient['quantity'];

            // Ha nincs a hűtőben, vagy kevesebb van, mint amennyi kellene
            if (!isset($fridgeItems[$ingredientId]) || $fridgeItems[$ingredientId]['quantity'] < $requiredQuantity) {
                $missingCount++;
            }
        }

        return $missingCount;
    }

    /**
     * GET /recipes/recommend/ai - AI alapú recept ajánlás kérése
     */
    public function aiRecommend(): void
    {
        // Csak bejelentkezett felhasználók kaphatnak ajánlást
        Auth::requireLogin();

        $userId = (int)($_SESSION['user_id'] ?? 0);

        try {
            // 1. Hűtő tartalmának lekérdezése mértékegységekkel
            $fridgeItems = $this->getUserFridgeItems($userId);
            if (empty($fridgeItems)) {
                $_SESSION['flash_error'] = 'A hűtőd üres, tölts fel pár alapanyagot, hogy ajánlhassunk recepteket!';
                header('Location: /fridge');
                exit;
            }

            // 2. Alapanyagok nevének és mértékegységeinek lekérése
            $ingredientData = $this->getIngredientNames(array_keys($fridgeItems));

            // 3. Összekapcsoljuk a mértékegység információkat az alapanyagokkal
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

            // 4. Gemini API kérés előkészítése
            $apiKey = $_ENV['GOOGLE_GEMINI'] ?? '';
            if (empty($apiKey)) {
                $_SESSION['flash_error'] = 'Az AI szolgáltatás jelenleg nem elérhető. Kérjük, próbáld meg később!';
                header('Location: /recipes/recommend');
                exit;
            }

            // 5. Recept ajánlás kérése a Gemini API-tól a teljes alapanyag adatokkal
            $recommendedRecipe = $this->getAIRecommendation($apiKey, $completeIngredientData);

            // 6. Az eredmény megjelenítése
            // Csak a nevek kinyerése az összetett adatstruktúrából
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
            $_SESSION['flash_error'] = 'Hiba történt az AI recept ajánlás betöltése közben: ' . $e->getMessage();
            header('Location: /recipes/recommend');
            exit;
        }
    }

    /**
     * Alapanyag nevek és adatok lekérdezése ID-k alapján
     *
     * @param array $ingredientIds Az alapanyag azonosítók
     * @return array Az alapanyag adatok [id => [name, unit, quantity]]
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

        // Paraméterek megadása a IN clause-hoz
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
     * Recept ajánlás kérése a Gemini API-tól
     *
     * @param string $apiKey A Gemini API kulcs
     * @param array $ingredients Az alapanyagok nevei
     * @return array A javasolt recept adatai
     */
    private function getAIRecommendation(string $apiKey, array $ingredients): array
    {
        // Új formázás: név és mértékegység információk rendezett összegyűjtése a prompthoz
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

        // Gemini PHP kliens létrehozása
        $client = Gemini::client($apiKey);

        // Generatív modell definiálása
        $model = $client->generativeModel(model: 'gemini-1.5-flash');

        // Mértékegység preferenciák szöveges formában történő előkészítése
        $unitPreferencesText = '';
        if (!empty($unitPreferences)) {
            $unitPrefItems = [];
            foreach ($unitPreferences as $ingredient => $unit) {
                $unitPrefItems[] = "$ingredient: $unit";
            }
            $unitPreferencesText = implode(', ', $unitPrefItems);
        } else {
            $unitPreferencesText = "nincs megadott preferencia";
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
            // Tartalom generálása
            $result = $model->generateContent($prompt);

            // Válasz szövegének kinyerése
            $textResponse = $result->text();

            // JSON adatok kinyerése a válaszból
            $jsonStart = strpos($textResponse, '{');
            $jsonEnd = strrpos($textResponse, '}');

            if ($jsonStart !== false && $jsonEnd !== false) {
                $jsonString = substr($textResponse, $jsonStart, $jsonEnd - $jsonStart + 1);
                $recipeData = json_decode($jsonString, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    return $recipeData;
                }
            }

            // Fallback: ha a JSON feldolgozás sikertelen
            // Átalakítjuk a hozzávalókat strukturált formátumba
            $formattedIngredients = [];
            foreach ($ingredientsForPrompt as $index => $name) {
                $formattedIngredients[] = [
                    'name' => $name,
                    'quantity' => '1',
                    'unit' => 'db'
                ];
            }

            return [
                'title' => 'Ajánlott recept',
                'ingredients' => $formattedIngredients,
                'instructions' => $textResponse,
                'preparationTime' => '30',
                'cookTime' => '20',
                'description' => 'Automatikusan generált recept a rendelkezésre álló alapanyagokból.'
            ];
        } catch (\Exception $e) {
            // Hiba esetén egyszerűsített recept ajánlás
            $formattedIngredients = [];
            foreach ($ingredientsForPrompt as $index => $name) {
                $formattedIngredients[] = [
                    'name' => $name,
                    'quantity' => '1',
                    'unit' => 'db'
                ];
            }

            return [
                'title' => 'Ajánlott egyszerű recept',
                'ingredients' => $formattedIngredients,
                'instructions' => 'Sajnos most nem sikerült recept ajánlást generálni. Próbáld meg később újra.',
                'preparationTime' => 'ismeretlen',
                'cookTime' => 'ismeretlen',
                'description' => 'A recept ajánlás jelenleg nem elérhető.'
            ];
        }
    }

    /**
     * POST /recipes/save-ai-recipe - AI által generált recept mentése az adatbázisba
     */
    public function saveAiRecipe(): void
    {
        // Csak bejelentkezett felhasználók menthetnek receptet
        Auth::requireLogin();

        // CSRF ellenőrzés
        if (!isset($_POST['csrf']) || !\WebDevProject\Security\Csrf::check($_POST['csrf'])) {
            $_SESSION['flash_error'] = 'Érvénytelen CSRF token. Kérjük, próbálja újra.';
            header('Location: /recipes/recommend/ai');
            exit;
        }

        $userId = (int)($_SESSION['user_id'] ?? 0);

        // Recept adatok ellenőrzése
        if (empty($_POST['recipe_data']) || empty($_POST['recipe_title']) || empty($_POST['recipe_category'])) {
            $_SESSION['flash_error'] = 'Hiányzó adatok a recept mentéséhez.';
            header('Location: /recipes/recommend/ai');
            exit;
        }

        try {
            // JSON adatok dekódolása
            $recipeData = json_decode($_POST['recipe_data'], true);
            if (json_last_error() !== JSON_ERROR_NONE || empty($recipeData)) {
                throw new \Exception('Érvénytelen recept adatok.');
            }

            // Recept kategória azonosító lekérése vagy létrehozása
            $categoryId = Recipe::getCategoryId($this->pdo, $_POST['recipe_category']);

            // Recept adatok összeállítása
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

            // Recept létrehozása az adatbázisban
            $recipeId = Recipe::create($this->pdo, $newRecipe);

            // Hozzávalók feldolgozása és mentése
            if (!empty($recipeData['ingredients']) && is_array($recipeData['ingredients'])) {
                $ingredients = [];

                foreach ($recipeData['ingredients'] as $ingredient) {
                    // Ellenőrizzük, hogy a hozzávaló új formátumban van-e (tömbként)
                    if (is_array($ingredient) && isset($ingredient['name'])) {
                        $ingredientName = $ingredient['name'];
                        $quantity = $this->parseQuantity($ingredient['quantity'] ?? '1');

                        // Hozzávalók megkeresése vagy létrehozása (név és egység is)
                        $ingredientId = $this->findOrCreateIngredient($ingredientName, $ingredient['unit'] ?? null);
                    } else {
                        // Régi formátum támogatása (csak string)
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

            $_SESSION['flash'] = 'AI által generált recept sikeresen elmentve!';
            header('Location: /recipe/' . $recipeId);
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Hiba történt a recept mentése közben: ' . $e->getMessage();
            header('Location: /recipes/recommend/ai');
        }

        exit;
    }

    /**
     * Hozzávaló keresése vagy létrehozása név és egység alapján
     *
     * @param string $name A hozzávaló neve
     * @param string|null $unit A mértékegység, ha van
     * @return int|null A hozzávaló azonosítója vagy null hiba esetén
     */
    private function findOrCreateIngredient(string $name, ?string $unit = null): ?int
    {
        $name = trim($name);
        if (empty($name)) {
            return null;
        }

        try {
            // Keresünk meglévő hozzávalót
            $stmt = $this->pdo->prepare("SELECT id FROM ingredients WHERE name = :name LIMIT 1");
            $stmt->execute(['name' => $name]);

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result) {
                // Ha van megadva egység és ez létező hozzávaló, frissítjük az egységet, ha még nincs beállítva
                if (!empty($unit)) {
                    $unitId = $this->getOrCreateUnit($unit);

                    // Ellenőrizzük, hogy van-e már egysége a hozzávalónak
                    $checkUnit = $this->pdo->prepare("SELECT unit_id FROM ingredients WHERE id = :id");
                    $checkUnit->execute(['id' => $result['id']]);
                    $currentUnit = $checkUnit->fetch(\PDO::FETCH_ASSOC);

                    // Ha nincs egysége, akkor beállítjuk
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

            // Ha nincs, létrehozunk egyet
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
            error_log('Hiba a hozzávaló keresése/létrehozása közben: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Mértékegység keresése vagy létrehozása név alapján
     *
     * @param string $unitName A mértékegység neve vagy rövidítése
     * @return int|null A mértékegység azonosítója vagy null hiba esetén
     */
    private function getOrCreateUnit(string $unitName): ?int
    {
        $unitName = trim($unitName);
        if (empty($unitName)) {
            return null;
        }

        try {
            // Normalizáljuk az egységnevet
            $normalizedUnitName = $this->normalizeUnitName($unitName);

            // Keresünk meglévő egységet név vagy rövidítés alapján
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

            // Ha nincs, létrehozunk egyet
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
            error_log('Hiba a mértékegység keresése/létrehozása közben: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Egységnév normalizálása (rövidítés -> teljes név)
     *
     * @param string $unitName Az egység neve vagy rövidítése
     * @return string A normalizált egységnév
     */
    private function normalizeUnitName(string $unitName): string
    {
        $unitMap = [
            'g' => 'gramm',
            'dkg' => 'dekagramm',
            'kg' => 'kilogramm',
            'ml' => 'milliliter',
            'dl' => 'deciliter',
            'l' => 'liter',
            'db' => 'darab',
            'cs' => 'csomag',
            'ek' => 'evőkanál',
            'tk' => 'teáskanál',
            'csp' => 'csipet',
            'fej' => 'fej',
            'gerezd' => 'gerezd',
            'szelet' => 'szelet',
            'csésze' => 'csésze',
            'bögre' => 'bögre',
            'marék' => 'marék',
            'közepes' => 'közepes',
            'nagy' => 'nagy',
            'kicsi' => 'kicsi'
        ];

        $unitName = strtolower($unitName);
        return $unitMap[$unitName] ?? $unitName;
    }

    /**
     * Teljes egységnév meghatározása
     *
     * @param string $unitName Az egység neve
     * @return string A teljes egységnév
     */
    private function getFullUnitName(string $unitName): string
    {
        // Ha az egység már teljes név, akkor csak visszaadjuk
        if (strlen($unitName) > 5) {
            return $unitName;
        }

        // Különben megnézzük, hogy ismert rövidítés-e
        return $this->normalizeUnitName($unitName);
    }

    /**
     * Mennyiség értelmezése szöveges formátumból
     *
     * @param string $quantityStr A mennyiség szöveges formában
     * @return float A feldolgozott mennyiség számként
     */
    private function parseQuantity(string $quantityStr): float
    {
        // Eltávolítjuk a nem-numerikus karaktereket, kivéve a tizedespontot/vesszőt
        $quantityStr = trim($quantityStr);

        // Ha üres, akkor 1.0 az alapértelmezett
        if (empty($quantityStr)) {
            return 1.0;
        }

        // Törtszámok kezelése (pl. "1/2")
        if (strpos($quantityStr, '/') !== false) {
            $parts = explode('/', $quantityStr);
            if (count($parts) == 2 && is_numeric($parts[0]) && is_numeric($parts[1]) && $parts[1] != 0) {
                return (float)$parts[0] / (float)$parts[1];
            }
        }

        // Egyéb esetekben megpróbáljuk számként értelmezni
        // Vessző cseréje pontra a tizedesjelnél
        $quantityStr = str_replace(',', '.', $quantityStr);

        // Csak számjegyeket és tizedespontot tartunk meg
        $quantityStr = preg_replace('/[^0-9.]/', '', $quantityStr);

        if (is_numeric($quantityStr)) {
            return (float)$quantityStr;
        }

        // Ha nem sikerült feldolgozni, az alapértelmezett érték 1.0
        return 1.0;
    }

    /**
     * Egyszerű nézet-render hívó
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
