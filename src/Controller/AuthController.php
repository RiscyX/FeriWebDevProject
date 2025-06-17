<?php

namespace WebDevProject\Controller;

use JetBrains\PhpStorm\NoReturn;
use WebDevProject\Form\RegisterForm;
use WebDevProject\Model\User;
use DateTime;
use PDOException;

class AuthController
{
    public function __construct(
        private \PDO $pdo
    ) {
    }

    public function authRegister(): void
    {
        $form = new RegisterForm($this->pdo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $form->formLoad($_POST);

            if ($form->formValidate()) {
                $newUserId = $form->formRegister();
                if ($newUserId !== null) {
                    $userModel = new User($this->pdo);
                    $sent = $userModel->sendVerification(
                        $newUserId,
                        $form->formGetValue('email')
                    );

                    if ($sent) {
                        $_SESSION['success'] =
                            'Sikeres regisztráció! Kérlek, ellenőrizd az e-mail fiókodat.';
                    } else {
                        $_SESSION['success'] =
                            'Regisztráció rendben, de az e-mail küldése nem sikerült.';
                    }
                } else {
                    $form->formGetErrors()[] = 'Ismeretlen hiba a regisztráció során.';
                }
            }
        }

        $formHtml = $form->formRender();
        include __DIR__ . '/../View/pages/auth/register.php';
    }

    // src/Controller/AuthController.php

    public function authLogin(): void
    {
        $form = new \WebDevProject\Form\LoginForm($this->pdo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $form->formLoad($_POST);
            if ($form->formValidate()) {
                $user = $form->formLogin();
                if ($user) {
                    // sikeres belépés: session-be
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    header('Location: index');
                    exit;
                } else {
                    $form->getErrors()[] = 'Hibás e-mail vagy jelszó.';
                }
            }
        }

        $formHtml = $form->formRender();
        include __DIR__ . '/../View/pages/auth/login.php';
    }

    #[NoReturn] public function authLogout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();

        header('Location: /FeriWebDevProject/public_html/');
        exit;
    }


    /**
     * @throws \Exception
     */
    public function authVerify(): void
    {
        $message = '';
        $type = 'success';

        if (empty($_GET['token'])) {
            http_response_code(404);
            $message = 'Érvénytelen hivatkozás: hiányzó token.';
            $type = 'danger';
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
            $type = 'danger';
            include __DIR__ . '/../View/pages/auth/verify.php';
            return;
        }

        if (!$row) {
            http_response_code(404);
            $message = 'Érvénytelen vagy már felhasznált token.';
            $type = 'warning';
        } else {
            $created = new DateTime($row['created_at']);
            $now = new DateTime();
            if ($now->getTimestamp() - $created->getTimestamp() > 24 * 3600) {
                http_response_code(410); // Gone
                $message = 'A verifikációs link lejárt (több mint 24 óra).';
                $type = 'warning';
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
            }
        }

        include __DIR__ . '/../View/pages/auth/verify.php';
    }
}
