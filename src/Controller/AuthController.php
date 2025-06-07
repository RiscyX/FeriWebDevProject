<?php

namespace WebDevProject\Controller;

use WebDevProject\Form\RegisterForm;
use WebDevProject\Model\User;
use DateTime;
use PDOException;

class AuthController
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function register(): void
    {
        $form = new RegisterForm($this->pdo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $form->load($_POST);

            if ($form->validate()) {
                $newUserId = $form->register();
                if ($newUserId !== null) {
                    $userModel = new User($this->pdo);
                    $sent = $userModel->sendVerification(
                        $newUserId,
                        $form->getValue('email')
                    );

                    if ($sent) {
                        $_SESSION['success'] =
                            'Sikeres regisztráció! Kérlek, ellenőrizd az e-mail fiókodat.';
                    } else {
                        $_SESSION['success'] =
                            'Regisztráció rendben, de az e-mail küldése nem sikerült.';
                    }
                } else {
                    $form->getErrors()[] = 'Ismeretlen hiba a regisztráció során.';
                }
            }
        }

        $formHtml = $form->render();
        include __DIR__ . '/../View/pages/auth/register.php';
    }

    public function verify(): void
    {
        $message = '';
        $type    = 'success';

        if (empty($_GET['token'])) {
            http_response_code(404);
            $message = 'Érvénytelen hivatkozás: hiányzó token.';
            $type    = 'danger';
            include __DIR__ . '/../View/pages/auth/verify.php';
            return;
        }

        $token = trim($_GET['token']);

        try {
            $stmt = $this->pdo->prepare("
            SELECT user_id, created_at
            FROM email_verifications
            WHERE token = :t
            LIMIT 1
        ");
            $stmt->execute([':t' => $token]);
            $row = $stmt->fetch();
        } catch (PDOException $e) {
            http_response_code(500);
            $message = 'Adatbázis-hiba: ' . $e->getMessage();
            $type    = 'danger';
            include __DIR__ . '/../View/pages/auth/verify.php';
            return;
        }

        if (! $row) {
            http_response_code(404);
            $message = 'Érvénytelen vagy már felhasznált token.';
            $type    = 'warning';
        } else {
            $created = new DateTime($row['created_at']);
            $now     = new DateTime();
            if ($now->getTimestamp() - $created->getTimestamp() > 24 * 3600) {
                http_response_code(410); // Gone
                $message = 'A verifikációs link lejárt (több mint 24 óra).';
                $type    = 'warning';
            } else {
                $upd = $this->pdo->prepare("
                UPDATE users
                SET email_verified_at = NOW()
                WHERE id = :uid
            ");
                $upd->execute([':uid' => $row['user_id']]);

                $del = $this->pdo->prepare("
                DELETE FROM email_verifications
                WHERE token = :t
            ");
                $del->execute([':t' => $token]);

                $message = 'Sikeres e-mail megerősítés! Most már bejelentkezhetsz.';
                $type    = 'success';
            }
        }

        include __DIR__ . '/../View/pages/auth/verify.php';
    }
}
