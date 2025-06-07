<?php

namespace WebDevProject\Helper;

class NavHelper
{
    public static function getNavItems(): array
    {
        $anon_nav = [
            ['label' => 'Kezdőlap',    'href' => '/index.php'],
            ['label' => 'Receptek',    'href' => '/recipes.php'],
            ['label' => 'Bejelentkezés','href' => '/login.php'],
            ['label' => 'Regisztráció', 'href' => '/register.php'],
        ];
        $user_nav = [
            ['label' => 'Kezdőlap',       'href' => '/index.php'],
            ['label' => 'Receptek',       'href' => '/recipes.php'],
            ['label' => 'Hűtőszekrényem','href' => '/fridge.php'],
            ['label' => 'Heti menü',      'href' => '/menu.php'],
            ['label' => 'Profil',         'href' => '/profile.php'],
            ['label' => 'Kijelentkezés',  'href' => '/logout.php'],
        ];
        $admin_nav = [
            ['label' => 'Kezdőlap',         'href' => '/index.php'],
            ['label' => 'Receptek',         'href' => '/recipes.php'],
            ['label' => 'Hűtőszekrényem',  'href' => '/fridge.php'],
            ['label' => 'Heti menü',        'href' => '/menu.php'],
            ['label' => 'Admin Dashboard',  'href' => '/admin/dashboard.php'],
            ['label' => 'Kategóriák',       'href' => '/admin/categories.php'],
            ['label' => 'Hozzávalók',       'href' => '/admin/ingredients.php'],
            ['label' => 'Felhasználók',     'href' => '/admin/users.php'],
            ['label' => 'Profil',           'href' => '/profile.php'],
            ['label' => 'Kijelentkezés',    'href' => '/logout.php'],
        ];
        if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            return $admin_nav;
        }

        if (!empty($_SESSION['user_id'])) {
            return $user_nav;
        }

        return $anon_nav;
    }
}
