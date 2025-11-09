<?php

declare(strict_types=1);

namespace WebDevProject\Controller;

class HomeController
{
    public function __construct(
        protected \PDO $pdo
    ) {
    }

    /**
     * @return void
     */
    public function index(): void
    {

        $title = 'Kezdőlap';
        ob_start();
        include __DIR__ . '/../View/pages/home.php';

        include __DIR__ . '/../View/layout.php';
    }
}
