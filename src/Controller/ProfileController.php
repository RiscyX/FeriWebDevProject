<?php

declare(strict_types=1);

namespace WebDevProject\Controller;

use WebDevProject\Core\Auth;
use WebDevProject\Model\Recipe;
use WebDevProject\Model\User;

/**
 * ProfileController
 */
class ProfileController
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * GET /profile - User profile
     * @return void
     */
    public function index(): void
    {
        Auth::requireLogin();
        $userId = (int)($_SESSION['user_id'] ?? 0);

        $user = User::getById($this->pdo, $userId);

        $favoriteRecipes = Recipe::getUserFavorites($this->pdo, $userId);

        foreach ($favoriteRecipes as &$recipe) {
            $recipe['name'] = $recipe['title'];
            $recipe['image'] = $recipe['image_path'] ?? '/assets/slide' . (($recipe['id'] % 3) + 1) . '.png';

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
     * POST /profile/favorites/add - Add recipe to favorites
     * @return void
     */
    public function addToFavorites(): void
    {
        Auth::requireLogin();

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

        $referer = $_SERVER['HTTP_REFERER'] ?? '/recipes';
        header('Location: ' . $referer);
        exit;
    }

    /**
     * POST /profile/favorites/remove - Remove recipe from favorites
     * @return void
     */
    public function removeFromFavorites(): void
    {
        Auth::requireLogin();

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

        $referer = $_SERVER['HTTP_REFERER'] ?? '/profile';
        header('Location: ' . $referer);
        exit;
    }


    /**
     * @param array $vars
     * @return void
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
