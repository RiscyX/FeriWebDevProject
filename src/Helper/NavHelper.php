<?php

declare(strict_types=1);

namespace WebDevProject\Helper;

class NavHelper
{
    /**
     * Return the navbar depending on the user's role.
     * @return array
     */
    public static function getNavItems(): array
    {
        $prefix = "";

        $common = [
            ['label' => 'Receptek', 'href' => $prefix . '/recipes'],
        ];

        $anonExtra = [
            ['label' => 'Bejelentkezés', 'href' => $prefix . '/login'],
            ['label' => 'Regisztráció', 'href' => $prefix . '/register'],
        ];

        $userExtra = [
            ['label' => 'Hűtőszekrényem', 'href' => $prefix . '/fridge'],
            ['label' => 'Ajánlott receptek', 'href' => $prefix . '/recipes/recommend'],
            ['label' => 'Heti menü', 'href' => $prefix . '/menus'],
            ['label' => 'Profil', 'href' => $prefix . '/profile'],
            ['label' => 'Kijelentkezés', 'href' => $prefix . '/logout'],
        ];

        $adminExtra = [
            ['label' => 'Felhasználók', 'href' => $prefix . '/admin/users'],
        ];

        if (!empty($_SESSION['role']) && $_SESSION['role'] === 1) {
            return array_merge($adminExtra, $common, $userExtra);
        }

        if (!empty($_SESSION['user_id'])) {
            return array_merge($common, $userExtra);
        }

        return array_merge($common, $anonExtra);
    }
}
