<?php

namespace WebDevProject\Helper;

class NavHelper
{
    public static function getNavItems(): array
    {
        $prefix = "FeriWebDevProject/public_html";

        $common = [
            ['label' => 'Receptek', 'href' => $prefix . '/recipes'],
        ];

        $anonExtra = [
            ['label' => 'Bejelentkezés', 'href' => $prefix . '/login'],
            ['label' => 'Regisztráció', 'href' => $prefix . '/register'],
        ];

        $userExtra = [
            ['label' => 'Hűtőszekrényem', 'href' => $prefix . '/fridge'],
            ['label' => 'Heti menü', 'href' => $prefix . '/menu'],
            ['label' => 'Profil', 'href' => $prefix . '/profile'],
            ['label' => 'Kijelentkezés', 'href' => $prefix . '/logout'],
        ];

        $adminExtra = [
            ['label' => 'Admin Dashboard', 'href' => $prefix . '/admin/dashboard'],
            ['label' => 'Kategóriák', 'href' => $prefix . '/admin/categories'],
            ['label' => 'Hozzávalók', 'href' => $prefix . '/admin/ingredients'],
            ['label' => 'Felhasználók', 'href' => $prefix . '/admin/users'],
        ];

        if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            return array_merge($common, $userExtra, $adminExtra);
        }

        if (!empty($_SESSION['user_id'])) {
            return array_merge($common, $userExtra);
        }

        return array_merge($common, $anonExtra);
    }
}
