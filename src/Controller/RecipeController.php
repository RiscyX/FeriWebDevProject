<?php

namespace WebDevProject\Controller;

use PDO;
use WebDevProject\Core\Auth;
use WebDevProject\Config\Config;
use WebDevProject\Model\Recipe;

class RecipeController
{
    public function __construct(
        private PDO $pdo
    ) {
        // Nem szükséges bejelentkezés a receptek megtekintéséhez
    }

    /**
     * GET /recipes - Receptek listázása
     */
    public function index(): void
    {
        try {
            // Receptek betöltése adatbázisból
            $recipes = \WebDevProject\Model\Recipe::getAll($this->pdo);

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
                    'description' => 'Klasszikus magyar étel, ami egyszerűen és gyorsan elkészíthető.'
                ],
                [
                    'id' => 2,
                    'name' => 'Túrós Csusza',
                    'category' => 'Főétel',
                    'image' => '/assets/slide2.png',
                    'created_by' => 'gasztro_guru',
                    'description' => 'Tejfölös-túrós tészta szalonnával, igazi magyaros étel.'
                ],
                [
                    'id' => 3,
                    'name' => 'Gyümölcssaláta',
                    'category' => 'Desszert',
                    'image' => '/assets/slide3.png',
                    'created_by' => 'vitamin_lover',
                    'description' => 'Frissítő, vitamindús gyümölcssaláta, tökéletes a nyári napokra.'
                ]
            ];
        }

        $this->render(compact('recipes'));
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
                'instructions' => 'Ez egy példa recept leírás. Az adatbázisból való betöltés sikertelen volt.'
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
