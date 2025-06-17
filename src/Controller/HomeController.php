<?php

namespace WebDevProject\Controller;

class HomeController
{
    public function __construct(private \PDO $pdo)
    {
    }

    /**
     * GET  /
     */
    public function index(): void
    {
        /* ------------------------------
         * 1) Oldal-specifikus adatok
         *    (ha kellene valami a DB-ből, itt kérdeznéd le)
         * ------------------------------ */
        $title = 'Kezdőlap';

        /* ------------------------------
         * 2) View összeállítása
         *    A home.php saját maga ob_start()-tal
         *    $content-be gyűjti a HTML-t.
         * ------------------------------ */
        ob_start();
        include __DIR__ . '/../View/pages/home.php';   // itt keletkezik $content
        /*  home.php végén:  $content = ob_get_clean();  $title = '...'; */

        /* ------------------------------
         * 3) Layout betöltése
         * ------------------------------ */
        include __DIR__ . '/../View/layout.php';
        /*  layout.php-nak elég a két változó ($title, $content) */
    }
}
