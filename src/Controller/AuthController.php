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
        $form = new \WebDevProject\Form\RegisterForm($this->pdo);

        /* ------------ 1)  Feldolgozás / POST ------------ */
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $form->formLoad($_POST);

            if ($form->formValidate()) {
                $newUserId = $form->formRegister();          // ↲ új user ID
                if ($newUserId !== null) {
                    $userModel = new \WebDevProject\Model\User($this->pdo);
                    $sent = $userModel->sendVerification(
                        $newUserId,
                        $form->formGetValue('email')
                    );

                    $_SESSION['flash'] = $sent
                        ? 'Sikeres regisztráció! Kérlek, ellenőrizd az e-mail fiókodat.'
                        : 'Regisztráció sikerült, de az e-mailt nem sikerült elküldeni.';

                    /* PRG-minta → redirect, hogy F5 ne küldje újra a POST-ot */
                    header('Location: /register');
                    exit;
                }

                // ismeretlen hiba
                $err = &$form->formGetErrors();
                $err[] = 'Ismeretlen hiba a regisztráció során.';
            }
        }

        /* ------------ 2)  View → $content ------------ */
        $formHtml = $form->formRender();

        ob_start();
        include __DIR__ . '/../View/pages/auth/register.php';
        $content = ob_get_clean();
        $title   = 'Regisztráció';

        /* ------------ 3)  Layout ------------ */
        include __DIR__ . '/../View/layout.php';
    }


    // src/Controller/AuthController.php

    public function authLogin(): void
    {
        $form = new \WebDevProject\Form\LoginForm($this->pdo);

        /* -------- 1)  Feldolgozás -------- */
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $form->formLoad($_POST);

            if ($form->formValidate()) {
                $user = $form->formLogin();

                if ($user) {                       // sikeres login
                    $_SESSION['user_id']  = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    header('Location: /');         // abszolút gyökérre
                    exit;
                }

                // sikertelen login → hiba a formhoz
                $errors = &$form->getErrors();     // REFERENCIÁVAL tér vissza
                $errors[] = 'Hibás e-mail vagy jelszó.';
            }
        }

        /* -------- 2)  View → buffer -------- */
        $formHtml = $form->formRender();           // → változó a view-nak

        ob_start();                                // 2/a: puffer indítása
        include __DIR__ . '/../View/pages/auth/login.php';
        $content = ob_get_clean();                 // 2/b: HTML → $content
        $title   = 'Bejelentkezés';                // <title> a layoutnak

        /* -------- 3)  Layout betöltése ---- */
        include __DIR__ . '/../View/layout.php';
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


    public function authVerify(): bool
    {
        $type    = 'success';
        $message = '';

        /* -------- 1)  Token ellenőrzés -------- */
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
            $message = 'Adatbázis-hiba: ' . $e->getMessage();
            $this->renderVerify($type, $message);
            return false;
        }

        /* -------- 2)  Feldolgozás -------- */
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
                /* siker → user frissítése */
                $this->pdo->prepare(
                    'UPDATE users SET email_verified_at = NOW() WHERE id = :uid'
                )->execute([':uid' => $row['user_id']]);

                $this->pdo->prepare(
                    'DELETE FROM email_verifications WHERE token = :t'
                )->execute([':t' => $token]);

                $message = 'Sikeres e-mail-megerősítés! Most már bejelentkezhetsz.';
            }
        }

        /* -------- 3)  Megjelenítés -------- */
        $this->renderVerify($type, $message);
        return true;
    }

    private function renderVerify(string $type, string $message): void
    {
        $title = 'E-mail megerősítése';

        ob_start();
        include __DIR__ . '/../View/pages/auth/verify.php';
        $content = ob_get_clean();

        include __DIR__ . '/../View/layout.php';
    }
}
