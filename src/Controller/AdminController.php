<?php

declare(strict_types=1);

namespace WebDevProject\Controller;

use WebDevProject\Core\Auth;
use WebDevProject\Model\User;
use WebDevProject\Model\Recipe;
use WebDevProject\Security\Csrf;

/**
 * Egyetlen, “mindent vivő” AdminController.
 * - Konstruktorban admin-jogosultság ellenőrzés
 * - Közös helper: render(), json(), paging()
 * - Fülek: index()  → felhasználók lista
 *          recipes() → receptek lista
 *          banUser()/deleteUser(), createRecipe()/deleteRecipe() stb.
 */
class AdminController
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
        Auth::requireRole(1);
    }

    private function paging(): array
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = max(1, (int)($_GET['per_page'] ?? 2));
        $offset = ($page - 1) * $perPage;
        return [$page, $perPage, $offset];
    }

    private function render(array $vars = [], string $title = 'Admin', string $view = 'users'): void
    {
        extract($vars, EXTR_SKIP);
        ob_start();
        include __DIR__ . "/../View/pages/admin/{$view}.php";
        $content = ob_get_clean();

        include __DIR__ . '/../View/layout.php';
    }

    private function json(mixed $data, int $code = 200): never
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** GET /admin  vagy  /admin/users  */
    public function index(): void
    {
        [$page, $perPage, $offset] = $this->paging();

        $total       = User::count($this->pdo);
        $totalPages  = (int)ceil($total / $perPage);   // ← új!
        $users       = User::paginated($this->pdo, $perPage, $offset);

        $this->render(
            compact(
                'users',
                'page',          // ha kell, át is nevezheted currentPage-re
                'perPage',
                'total',
                'totalPages'     // ← új!
            ),
            'Felhasználók'
        );
    }

    /** POST /admin/users/ban   (fetch vagy form) */
    public function banUser(): never
    {
        // CSRF ellenőrzés
        if (!isset($_POST['csrf']) || !\WebDevProject\Security\Csrf::check($_POST['csrf'])) {
            http_response_code(403);
            die('CSRF token mismatch');
        }

        $id = (int)($_POST['id'] ?? 0);
        $ok = $id && User::ban($this->pdo, $id);

        if ($ok) {
            $_SESSION['flash'] = 'A felhasználó sikeresen bannolva lett.';
        } else {
            $_SESSION['flash'] = 'Hiba történt a felhasználó bannolása során.';
        }

        header('Location: /admin/users');
        exit;
    }

    /** POST /admin/users/unban   (fetch vagy form) */
    public function unbanUser(): never
    {
        // CSRF ellenőrzés
        if (!isset($_POST['csrf']) || !\WebDevProject\Security\Csrf::check($_POST['csrf'])) {
            http_response_code(403);
            die('CSRF token mismatch');
        }

        $id = (int)($_POST['id'] ?? 0);
        $ok = $id && User::unban($this->pdo, $id);

        if ($ok) {
            $_SESSION['flash'] = 'A felhasználó bannolása sikeresen feloldva.';
        } else {
            $_SESSION['flash'] = 'Hiba történt a felhasználó bannolásának feloldása során.';
        }

        header('Location: /admin/users');
        exit;
    }

    /** GET /admin/users/delete?id=123  (vagy DELETE metódus REST-esen) */
    public function deleteUser(): never
    {
        $id = (int)($_GET['id'] ?? 0);
        $ok = $id && User::delete($this->pdo, $id);
        $this->json(['ok' => $ok]);
    }

    /** GET /admin/recipes - Beküldött receptek kezelése */
    public function recipes(): void
    {
        [$page, $perPage, $offset] = $this->paging();

        try {
            // Nem jóváhagyott receptek lekérése
            $recipes = Recipe::getPendingRecipes($this->pdo, [
                'limit' => $perPage,
                'offset' => $offset
            ]);

            // Átalakítás a nézethez
            foreach ($recipes as &$recipe) {
                $recipe['name'] = $recipe['title'];
                $recipe['status'] = 'pending';

                // Alapértelmezett kép beállítása, ha nincs
                if (empty($recipe['image_path'])) {
                    $recipe['image'] = '/assets/slide' . (($recipe['id'] % 3) + 1) . '.png';
                } else {
                    $recipe['image'] = $recipe['image_path'];
                }
            }

            // Receptek számának lekérése
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM recipes WHERE verified_at IS NULL");
            $total = $stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;
        } catch (\Exception $e) {
            // Hiba esetén használjuk az alapértelmezett demo recepteket
            $recipes = [
                [
                    'id' => 10,
                    'name' => 'Túrógombóc',
                    'category' => 'Desszert',
                    'status' => 'pending', // pending, approved, rejected
                    'created_by' => 'felhasználó123',
                    'created_at' => '2025-06-28',
                ],
                [
                    'id' => 11,
                    'name' => 'Sült csirkecomb',
                    'category' => 'Főétel',
                    'status' => 'pending',
                    'created_by' => 'szakács456',
                    'created_at' => '2025-07-01',
                ]
            ];

            $total = count($recipes);
        }

        $totalPages = (int)ceil($total / $perPage);

        $this->render(
            compact(
                'recipes',
                'page',
                'perPage',
                'total',
                'totalPages'
            ),
            'Beküldött receptek',
            'recipes'
        );
    }

    /**
     * POST /admin/recipes/approve - Recept jóváhagyása
     */
    public function approveRecipe(): never
    {
        // CSRF ellenőrzés
        if (!isset($_POST['csrf']) || !\WebDevProject\Security\Csrf::check($_POST['csrf'])) {
            http_response_code(403);
            die('CSRF token mismatch');
        }

        $id = (int)($_POST['id'] ?? 0);
        $ok = $id && Recipe::approveRecipe($this->pdo, $id);

        if ($ok) {
            $_SESSION['flash'] = 'A recept sikeresen jóváhagyva.';
        } else {
            $_SESSION['flash'] = 'Hiba történt a recept jóváhagyása során.';
        }

        header('Location: /admin/recipes');
        exit;
    }

    /**
     * POST /admin/recipes/reject - Recept elutasítása
     */
    public function rejectRecipe(): never
    {
        // CSRF ellenőrzés
        if (!isset($_POST['csrf']) || !\WebDevProject\Security\Csrf::check($_POST['csrf'])) {
            http_response_code(403);
            die('CSRF token mismatch');
        }

        $id = (int)($_POST['id'] ?? 0);
        $ok = $id && Recipe::rejectRecipe($this->pdo, $id);

        if ($ok) {
            $_SESSION['flash'] = 'A recept sikeresen elutasítva.';
        } else {
            $_SESSION['flash'] = 'Hiba történt a recept elutasítása során.';
        }

        header('Location: /admin/recipes');
        exit;
    }

    public function usersApi(): never
    {
        // paging helper a Base-ben
        [$page, $perPage, $offset] = $this->paging();

        $total = User::count($this->pdo);
        $users = User::paginated($this->pdo, $perPage, $offset);

        $this->json([
            'data'       => $users,
            'pagination' => [
                'page'        => $page,
                'per_page'    => $perPage,
                'total'       => $total,
                'total_pages' => (int)ceil($total / $perPage),
            ],
        ]);
    }
}
