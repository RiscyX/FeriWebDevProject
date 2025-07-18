<?php

declare(strict_types=1);

namespace WebDevProject\Controller;

use JetBrains\PhpStorm\NoReturn;
use PHPMailer\PHPMailer\Exception;
use Random\RandomException;
use WebDevProject\Form\LoginForm;
use WebDevProject\Form\PasswordResetForm;
use WebDevProject\Form\RegisterForm;
use WebDevProject\Model\User;
use WebDevProject\Security\Csrf;

class AuthController
{
    public function __construct(
        private \PDO $pdo
    ) {
    }

    /**
     * @throws Exception
     */
    public function authRegister(): void
    {
        $form = new RegisterForm($this->pdo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::check($_POST['csrf'] ?? '')) {
                http_response_code(419); // CSRF token mismatch
                exit;
            }
            $form->formLoad($_POST);

            if ($form->formValidate()) {
                $newUserId = $form->formRegister();
                if ($newUserId !== null) {
                    $userModel = new User($this->pdo);
                    $sent = $userModel->sendVerification(
                        $newUserId,
                        $form->getValue('email')
                    );

                    $_SESSION['flash'] = $sent
                        ? 'Sikeres regisztráció! Kérlek, ellenőrizd az e-mail fiókodat.'
                        : 'Regisztráció sikerült, de az e-mailt nem sikerült elküldeni.';

                    header('Location: /register');
                    exit;
                }

                $form->addError('Unknown error during registration.');
            }
        }
        $formHtml = $form->formRender();

        ob_start();
        include __DIR__ . '/../View/pages/auth/register.php';
        $content = ob_get_clean();
        $title   = 'Regisztráció';

        include __DIR__ . '/../View/layout.php';
    }

    public function authLogin(): void
    {
        $form = new LoginForm($this->pdo);

        // If the user is banned, display an error message
        if (isset($_GET['banned']) && $_GET['banned'] == 1) {
            $form->addError('A fiókja bannolva lett. Kérjük, vegye fel a kapcsolatot az adminisztrátorral.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::check($_POST['csrf'] ?? '')) {
                http_response_code(419); // CSRF token mismatch
                exit;
            }
            $form->formLoad($_POST);

            if ($form->formValidate()) {
                $user = $form->formLogin();

                if ($user) {
                    $_SESSION['user_id']  = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    header('Location: /');
                    exit;
                }

                if (!$form->hasErrors()) {
                    $form->addError('Hibás e-mail vagy jelszó.');
                }
            }
        }

        $formHtml = $form->formRender();

        ob_start();
        include __DIR__ . '/../View/pages/auth/login.php';
        $content = ob_get_clean();
        $title   = 'Bejelentkezés';

        include __DIR__ . '/../View/layout.php';
    }


    public function authLogout(): never
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

        header('Location: /');
        exit;
    }


    /**
     * @throws \Exception
     */
    public function authVerify(): bool
    {
        $type    = 'success';

        $token = trim($_GET['token'] ?? '');

        if ($token === '') {
            http_response_code(404);
            $type    = 'danger';
            $message = 'Érvénytelen hivatkozás: hiányzó token.';
            $this->renderVerify($type, $message);
            return false;
        }

        try {
            $stmt = $this->pdo->prepare(
                'SELECT user_id, created_at FROM email_verifications
             WHERE token = :t LIMIT 1'
            );
            $stmt->execute([':t' => $token]);
            $row = $stmt->fetch();
        } catch (\PDOException $e) {
            http_response_code(500);
            $type    = 'danger';
            $message = 'Database error: ' . $e->getMessage();
            $this->renderVerify($type, $message);
            return false;
        }

        if (!$row) {
            http_response_code(404);
            $type    = 'warning';
            $message = 'Érvénytelen vagy már felhasznált token.';
        } else {
            $created = new \DateTime($row['created_at']);
            $now     = new \DateTime();

            if ($now->getTimestamp() - $created->getTimestamp() > 24 * 3600) {
                http_response_code(410);
                $type    = 'warning';
                $message = 'A verifikációs link lejárt (több mint 24 óra).';
            } else {
                $this->pdo->prepare(
                    'UPDATE users SET email_verified_at = NOW() WHERE id = :uid'
                )->execute([':uid' => $row['user_id']]);

                $this->pdo->prepare(
                    'DELETE FROM email_verifications WHERE token = :t'
                )->execute([':t' => $token]);

                $message = 'Sikeres e-mail-megerősítés! Most már bejelentkezhetsz.';
            }
        }

        $this->renderVerify($type, $message);
        return true;
    }

    /**
     * @param string $type
     * @param string $message
     * @return void
     */
    private function renderVerify(string $type, string $message): void
    {
        $title = 'E-mail megerősítése';

        ob_start();
        include __DIR__ . '/../View/pages/auth/verify.php';
        $content = ob_get_clean();

        include __DIR__ . '/../View/layout.php';
    }

    /**
     * @throws RandomException
     */
    public function authPasswordReset(): void
    {
        $token = $_GET['token'] ?? ($_POST['token'] ?? null);
        $form  = new PasswordResetForm($this->pdo, $token);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::check($_POST['csrf'] ?? '')) {
                http_response_code(419);
                exit;
            }
            $form->formLoad($_POST);

            $ok = $form->formValidate() && $form->formSubmit();

            $_SESSION['flash'] = $ok
                ? ($token
                    ? 'Sikeres jelszófrissítés! Most már bejelentkezhetsz.'
                    : 'Ha létezik ilyen fiók, elküldtük a visszaállító linket.')
                : 'Something went wrong. Please try again.';

            header('Location: ' . ($token ? '/login' : '/reset'), true, 303);
            exit;
        }


        ob_start();
        $formHtml = $form->formRender();
        include __DIR__ . '/../View/pages/auth/reset.php';
        $content = ob_get_clean();
        $title   = 'Új jelszó beállítása';
        include __DIR__ . '/../View/layout.php';
    }
}
