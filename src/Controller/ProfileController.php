<?php

declare(strict_types=1);

namespace WebDevProject\Controller;

use WebDevProject\Core\Auth;
use WebDevProject\Model\Recipe;
use WebDevProject\Model\User;

/**
 * ProfileController - Felhasználói profil kezelése
 */
class ProfileController
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * GET /profile - Felhasználói profil megjelenítése
     */
    public function index(): void
    {
        // Csak bejelentkezett felhasználók számára
        Auth::requireLogin();
        $userId = (int)($_SESSION['user_id'] ?? 0);

        // Felhasználói adatok betöltése
        $user = User::getById($this->pdo, $userId);

        // Kedvenc receptek betöltése
        $favoriteRecipes = Recipe::getUserFavorites($this->pdo, $userId);

        // Képek elérési útját és egyéb mezőket alakítsunk át a view-nak megfelelően
        foreach ($favoriteRecipes as &$recipe) {
            $recipe['name'] = $recipe['title'];
            $recipe['image'] = $recipe['image_path'] ?? '/assets/slide' . (($recipe['id'] % 3) + 1) . '.png';

            // Létrehoz egy rövidített változatot a leírásból
            if (mb_strlen($recipe['description']) > 100) {
                $recipe['short_description'] = mb_substr($recipe['description'], 0, 100) . '...';
            } else {
                $recipe['short_description'] = $recipe['description'];
            }
        }

        $this->render([
            'user' => $user,
            'favoriteRecipes' => $favoriteRecipes
        ]);
    }

    /**
     * POST /profile/favorites/add - Recept hozzáadása a kedvencekhez
     */
    public function addToFavorites(): void
    {
        // Csak bejelentkezett felhasználók számára
        Auth::requireLogin();

        // CSRF ellenőrzés
        if (!isset($_POST['csrf']) || !\WebDevProject\Security\Csrf::check($_POST['csrf'])) {
            $_SESSION['flash_error'] = 'Érvénytelen kérés. Próbáld újra!';
            header('Location: /recipes');
            exit;
        }

        $userId = (int)($_SESSION['user_id'] ?? 0);
        $recipeId = (int)($_POST['recipe_id'] ?? 0);

        if ($recipeId === 0) {
            $_SESSION['flash_error'] = 'Érvénytelen recept azonosító.';
            header('Location: /recipes');
            exit;
        }

        $success = Recipe::addToFavorites($this->pdo, $userId, $recipeId);

        if ($success) {
            $_SESSION['flash'] = 'A recept sikeresen hozzáadva a kedvencekhez!';
        } else {
            $_SESSION['flash_error'] = 'Hiba történt a recept kedvencekhez adásakor.';
        }

        // Visszatérés az előző oldalra, vagy ha nincs ilyen, akkor a receptek oldalra
        $referer = $_SERVER['HTTP_REFERER'] ?? '/recipes';
        header('Location: ' . $referer);
        exit;
    }

    /**
     * POST /profile/favorites/remove - Recept eltávolítása a kedvencekből
     */
    public function removeFromFavorites(): void
    {
        // Csak bejelentkezett felhasználók számára
        Auth::requireLogin();

        // CSRF ellenőrzés
        if (!isset($_POST['csrf']) || !\WebDevProject\Security\Csrf::check($_POST['csrf'])) {
            $_SESSION['flash_error'] = 'Érvénytelen kérés. Próbáld újra!';
            header('Location: /profile');
            exit;
        }

        $userId = (int)($_SESSION['user_id'] ?? 0);
        $recipeId = (int)($_POST['recipe_id'] ?? 0);

        if ($recipeId === 0) {
            $_SESSION['flash_error'] = 'Érvénytelen recept azonosító.';
            header('Location: /profile');
            exit;
        }

        $success = Recipe::removeFromFavorites($this->pdo, $userId, $recipeId);

        if ($success) {
            $_SESSION['flash'] = 'A recept sikeresen eltávolítva a kedvencekből!';
        } else {
            $_SESSION['flash_error'] = 'Hiba történt a recept kedvencekből való eltávolításakor.';
        }

        // Visszatérés az előző oldalra, vagy ha nincs ilyen, akkor a profil oldalra
        $referer = $_SERVER['HTTP_REFERER'] ?? '/profile';
        header('Location: ' . $referer);
        exit;
    }

    /**
     * Egyszerű nézet-render hívó
     */
    private function render(array $vars = []): void
    {
        extract($vars);
        $title = 'Felhasználói profil';

        ob_start();
        include __DIR__ . '/../View/pages/profile.php';
        $content = ob_get_clean();

        include __DIR__ . '/../View/layout.php';
    }
}
