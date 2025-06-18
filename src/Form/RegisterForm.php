<?php

// src/Form/RegisterForm.php
namespace WebDevProject\Form;

use WebDevProject\Model\User;

class RegisterForm
{
    /** @var string[] data from $_POST */
    private array $data = [];
    /** @var string[] validation errors array */
    private array $errors = [];

    public function __construct(
        protected \PDO $pdo
    ) {
    }

    public function formLoad(array $postData): void
    {
        $this->data['username'] = trim($postData['username'] ?? '');
        $this->data['email'] = trim($postData['email'] ?? '');
        $this->data['password'] = trim($postData['password'] ?? '');
        $this->data['password_confirm'] = trim($postData['password_confirm'] ?? '');
    }

    public function formValidate(): bool
    {
        $username = $this->data['username'] ?? '';
        $email = $this->data['email'] ?? '';
        $pass = $this->data['password'] ?? '';
        $pass2 = $this->data['password_confirm'] ?? '';
        if ($username === '' || strlen($username) < 3 || strlen($username) > 50) {
            $this->errors[] = 'A felhasználónév 3 és 50 karakter között kell legyen.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'Érvénytelen vagy hiányzó e-mail cím.';
        }
        if ($pass === '' || strlen($pass) < 6) {
            $this->errors[] = 'A jelszó legalább 6 karakter legyen.';
        }
        if ($pass !== $pass2) {
            $this->errors[] = 'A jelszavak nem egyeznek.';
        }

        if (empty($this->errors)) {
            $userModel = new User($this->pdo);
            if ($userModel->userExists($username, $email)) {
                $this->errors[] = 'Már létezik ilyen felhasználónév vagy e-mail.';
            }
        }

        return empty($this->errors);
    }

    public function &formGetErrors(): array
    {
        return $this->errors;
    }

    public function formRegister(): ?int
    {
        $username = $this->data['username'];
        $email = $this->data['email'];
        $pass = $this->data['password'];
        $userModel = new User($this->pdo);
        return $userModel->userRegister($username, $email, $pass);
    }

    public function formRender(): string
    {
        $html = '';
        if (!empty($this->errors)) {
            $html .= '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
            $html .= '<ul class="mb-0">';
            foreach ($this->errors as $err) {
                $html .= '<li>' . htmlspecialchars($err, ENT_QUOTES) . '</li>';
            }
            $html .= '</ul>';
            $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Bezárás"></button>';
            $html .= '</div>';
        }

        if (isset($_SESSION['success'])) {
            $html .= '<div class="alert alert-success alert-dismissible fade show" role="alert">';
            $html .= htmlspecialchars($_SESSION['success'], ENT_QUOTES);
            $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Bezárás"></button>';
            $html .= '</div>';
        }

        $html .= '<form method="post" class="container">';
        $html .= '<div class="row g-3">';

        /*─ Felhasználónév ─*/
        $html .= '<div class="col-12">';
        $html .= ' <label for="username" class="form-label fw-semibold text-dark">Felhasználónév</label>';
        $html .= ' <input type="text" name="username" id="username" placeholder="Felhasználónév"'
            . ' class="form-control fs-5 rounded-3 bg-light"'
            . ' value="' . htmlspecialchars($this->formGetValue("username"), ENT_QUOTES) . '"'
            . ' required minlength="3" maxlength="50">';
        $html .= '</div>';

        /*─ E-mail ─*/
        $html .= '<div class="col-12">';
        $html .= ' <label for="email" class="form-label fw-semibold text-dark">E-mail cím</label>';
        $html .= ' <input type="email" name="email" id="email" placeholder="E-mail cím"'
            . ' class="form-control fs-5 rounded-3 bg-light"'
            . ' value="' . htmlspecialchars($this->formGetValue("email"), ENT_QUOTES) . '"'
            . ' required>';
        $html .= '</div>';

        /*─ Jelszó ─*/
        $html .= '<div class="col-12">';
        $html .= ' <label for="password" class="form-label fw-semibold text-dark">Jelszó</label>';
        $html .= ' <input type="password" name="password" id="password" placeholder="Jelszó"'
            . ' class="form-control fs-5 rounded-3 bg-light" required>';
        $html .= '</div>';

        /*─ Jelszó megerősítés ─*/
        $html .= '<div class="col-12">';
        $html .= ' <label for="password_confirm" class="form-label fw-semibold text-dark">Jelszó megerősítése</label>';
        $html .= ' <input type="password" name="password_confirm"
                id="password_confirm" placeholder="Jelszó megerősítése"'
            . ' class="form-control fs-5 rounded-3 bg-light" required>';
        $html .= '</div>';

        /*─ Gomb + link ─*/
        $html .= '<div class="col-12 d-grid mb-3">';
        $html .= ' <button type="submit" class="btn btn-primary fs-5 py-2 rounded-pill shadow-sm">'
            . 'Regisztráció</button>';
        $html .= '</div>';
        $html .= '<div class="col-12">';
        $html .= ' <p class="text-center mb-0">Már van fiókod? '
            . '<a href="/login" class="link-success fw-semibold">Bejelentkezés</a></p>';
        $html .= '</div>';

        $html .= '</div></form>';
        return $html;
    }

    public function formGetValue(string $field): string
    {
        return $this->data[$field] ?? '';
    }
}
