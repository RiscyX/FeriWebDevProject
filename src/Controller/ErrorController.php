<?php

declare(strict_types=1);

namespace WebDevProject\Controller;

use PDO;
use Exception;

class ErrorController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function notFound(): void
    {
        http_response_code(404);
        $this->render('404');
    }

    public function methodNotAllowed(): void
    {
        http_response_code(405);
        $this->render('405');
    }

    public function forbidden(): void
    {
        http_response_code(403);
        $this->render('405');
    }

    private function render(string $view): void
    {
        $title = $view === '404' ? '404 - Oldal nem található' : '405 - Metódus nem engedélyezett';

        ob_start();
        include __DIR__ . "/../View/pages/{$view}.php";
        $content = ob_get_clean();

        include __DIR__ . '/../View/layout.php';
    }
}
