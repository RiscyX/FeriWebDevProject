<?php

// src/Form/RegisterForm.php
namespace WebDevProject\Form;

use WebDevProject\Model\User;

class RegisterForm
{
    /** @var string[] data from $_POST */
    private $data = [];
    /** @var string[] validation errors array */
    private $errors = [];

    public function __construct(
        private \PDO $pdo
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

        $html .= '<form action="register.php" method="post" novalidate class="d-grid gap-3">';
        $html .= '<div class="form-floating">';
        $html .= '  <input'
            . ' type="text"'
            . ' class="form-control fs-5"'
            . ' id="username"'
            . ' name="username"'
            . ' placeholder="Felhasználónév"'    // szükséges a lebegő labelhez
            . ' required minlength="3" maxlength="50"'
            . ' value="' . htmlspecialchars($this->formGetValue('username'), ENT_QUOTES) . '">';
        $html .= '  <label for="username">Felhasználónév (3-50 karakter)</label>';
        $html .= '</div>';
        $html .= '<div class="form-floating">';
        $html .= '  <input'
            . ' type="email"'
            . ' class="form-control fs-5"'
            . ' id="email"'
            . ' name="email"'
            . ' placeholder="E-mail cím"'
            . ' required'
            . ' value="' . htmlspecialchars($this->formGetValue('email'), ENT_QUOTES) . '">';
        $html .= '  <label for="email">E-mail cím</label>';
        $html .= '</div>';

// Jelszó
        $html .= '<div class="form-floating">';
        $html .= '  <input'
            . ' type="password"'
            . ' class="form-control fs-5"'
            . ' id="password"'
            . ' name="password"'
            . ' placeholder="Jelszó"'
            . ' required minlength="6">';
        $html .= '  <label for="password">Jelszó (minimum 6 karakter)</label>';
        $html .= '</div>';

// Jelszó megerősítése
        $html .= '<div class="form-floating">';
        $html .= '  <input'
            . ' type="password"'
            . ' class="form-control fs-5"'
            . ' id="password_confirm"'
            . ' name="password_confirm"'
            . ' placeholder="Jelszó megerősítése"'
            . ' required minlength="6">';
        $html .= '  <label for="password_confirm">Jelszó megerősítése</label>';
        $html .= '</div>';
        $html .= '<div class="d-grid mb-3">';
        $html .= '  <button type="submit" class="btn btn-primary fs-5">';
        $html .= '    Regisztráció';
        $html .= '  </button>';
        $html .= '</div>';
        $html .= '<div class="col-12"><hr class="my-3"></div>';
        $html .= '<div class="col-12">';
        $html .= '<p class="text-center mb-0">Már van fiókod? 
<a href="login.php" class="link-success fw-semibold">Bejelentkezés</a></p>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<hr class="my-3">';
        $html .= '<p class="text-center mb-0">';
        $html .= '  Már van fiókod? <a href="/login" class="link-info">Bejelentkezés</a>';
        $html .= '</p>';
        $html .= '</form>';
        return $html;
    }

    public function formGetValue(string $field): string
    {
        return $this->data[$field] ?? '';
    }
}
