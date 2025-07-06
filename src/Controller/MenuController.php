<?php

declare(strict_types=1);

namespace WebDevProject\Controller;

use PDO;
use WebDevProject\Core\Auth;

class MenuController
{
    public function __construct(
        private PDO $pdo
    ) {
        // Csak bejelentkezett felhasználók férhetnek hozzá a menühöz
        Auth::requireLogin();
    }

    /**
     * GET /menus - Menü kezelő oldal
     */
    public function index(): void
    {
        $userId = (int)$_SESSION['user_id'];
        $menus = $this->getMenus($userId);

        // Napok nevei magyarul a rendezéshez és a megjelenítéshez
        $dayOrder = [
            'Monday' => 1,
            'Tuesday' => 2,
            'Wednesday' => 3,
            'Thursday' => 4,
            'Friday' => 5,
            'Saturday' => 6,
            'Sunday' => 7
        ];

        $dayNames = [
            'Monday' => 'Hétfő',
            'Tuesday' => 'Kedd',
            'Wednesday' => 'Szerda',
            'Thursday' => 'Csütörtök',
            'Friday' => 'Péntek',
            'Saturday' => 'Szombat',
            'Sunday' => 'Vasárnap'
        ];

        // Rendezés napok szerint
        usort($menus, function ($a, $b) use ($dayOrder) {
            return $dayOrder[$a['day_of_week']] <=> $dayOrder[$b['day_of_week']];
        });

        $this->render([
            'menus' => $menus,
            'dayNames' => $dayNames
        ]);
    }

    /**
     * POST /menus/add - Recept hozzáadása a menühöz
     */
    public function addToMenu(): void
    {
        // CSRF ellenőrzés
        if (!isset($_POST['csrf']) || !\WebDevProject\Security\Csrf::check($_POST['csrf'])) {
            $_SESSION['flash_error'] = 'Érvénytelen CSRF token. Kérjük, próbálja újra.';
            header('Location: ' . $_SERVER['HTTP_REFERER'] ?? '/recipes');
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $recipeId = isset($_POST['recipe_id']) ? (int)$_POST['recipe_id'] : 0;
        $menuName = isset($_POST['menu_name']) ? trim($_POST['menu_name']) : '';
        $dayOfWeek = isset($_POST['day_of_week']) ? trim($_POST['day_of_week']) : '';

        // Validálás
        if (!$recipeId || !$menuName || !$dayOfWeek) {
            $_SESSION['flash_error'] = 'Minden mezőt ki kell tölteni!';
            header('Location: ' . $_SERVER['HTTP_REFERER'] ?? '/recipes');
            exit;
        }

        // Ellenőrizzük, hogy a recept létezik-e
        $recipeStmt = $this->pdo->prepare("SELECT id FROM recipes WHERE id = ?");
        $recipeStmt->execute([$recipeId]);

        if (!$recipeStmt->fetch()) {
            $_SESSION['flash_error'] = 'A recept nem található!';
            header('Location: ' . $_SERVER['HTTP_REFERER'] ?? '/recipes');
            exit;
        }

        // Ellenőrizzük, hogy a nap érvényes-e
        $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        if (!in_array($dayOfWeek, $validDays)) {
            $_SESSION['flash_error'] = 'Érvénytelen nap!';
            header('Location: ' . $_SERVER['HTTP_REFERER'] ?? '/recipes');
            exit;
        }

        try {
            // Recept hozzáadása a menühöz
            $stmt = $this->pdo->prepare("
                INSERT INTO menus (user_id, name, recipe_id, day_of_week)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $menuName, $recipeId, $dayOfWeek]);

            $_SESSION['flash'] = 'Recept sikeresen hozzáadva a menühöz!';
        } catch (\PDOException $e) {
            $_SESSION['flash_error'] = 'Hiba történt a recept menühöz adása közben!';
            error_log('Menu add error: ' . $e->getMessage());
        }

        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? '/recipes');
        exit;
    }

    /**
     * POST /menus/remove - Recept eltávolítása a menüből
     */
    public function removeFromMenu(): void
    {
        // CSRF ellenőrzés
        if (!isset($_POST['csrf']) || !\WebDevProject\Security\Csrf::check($_POST['csrf'])) {
            $_SESSION['flash_error'] = 'Érvénytelen CSRF token. Kérjük, próbálja újra.';
            header('Location: /menus');
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $menuId = isset($_POST['menu_id']) ? (int)$_POST['menu_id'] : 0;

        if (!$menuId) {
            $_SESSION['flash_error'] = 'Érvénytelen menü azonosító!';
            header('Location: /menus');
            exit;
        }

        try {
            // Ellenőrizzük, hogy a felhasználó tulajdonosa-e a menünek
            $checkStmt = $this->pdo->prepare("
                SELECT id FROM menus WHERE id = ? AND user_id = ?
            ");
            $checkStmt->execute([$menuId, $userId]);

            if (!$checkStmt->fetch()) {
                $_SESSION['flash_error'] = 'Nincs jogosultságod ezt a menüt törölni!';
                header('Location: /menus');
                exit;
            }

            // Menü törlése
            $stmt = $this->pdo->prepare("DELETE FROM menus WHERE id = ? AND user_id = ?");
            $stmt->execute([$menuId, $userId]);

            $_SESSION['flash'] = 'Recept sikeresen eltávolítva a menüből!';
        } catch (\PDOException $e) {
            $_SESSION['flash_error'] = 'Hiba történt a recept menüből való eltávolítása közben!';
            error_log('Menu remove error: ' . $e->getMessage());
        }

        header('Location: /menus');
        exit;
    }

    /**
     * Felhasználó menüinek lekérdezése
     *
     * @param int $userId A felhasználó azonosítója
     * @return array A felhasználó menüi
     */
    private function getMenus(int $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT m.id, m.name, m.day_of_week, m.created_at,
                   r.id as recipe_id, r.title as recipe_name, r.description,
                   c.name as category, u.username as created_by,
                   rp.path as image_path
            FROM menus m
            JOIN recipes r ON m.recipe_id = r.id
            LEFT JOIN categories c ON r.category_id = c.id
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN recipe_picture rp ON r.id = rp.recipe_id
            WHERE m.user_id = ?
            ORDER BY m.day_of_week
        ");

        $stmt->execute([$userId]);
        $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Képek elérési útját alakítsuk át a view-nak megfelelően
        foreach ($menus as &$menu) {
            // Ha nincs kép, használjunk alapértelmezett képet
            $menu['image'] = $menu['image_path'] ?? '/assets/slide' . (($menu['recipe_id'] % 3) + 1) . '.png';
        }

        return $menus;
    }

    /**
     * Nézet-render hívó
     */
    private function render(array $vars = []): void
    {
        // Beállítjuk az oldal címét
        $vars['title'] = 'Menük';

        extract($vars, EXTR_SKIP);
        ob_start();
        include __DIR__ . "/../View/pages/menus.php";
        $content = ob_get_clean();

        include __DIR__ . '/../View/layout.php';
    }
}
