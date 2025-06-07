<?php

// src/Form/RegisterForm.php
namespace WebDevProject\Form;

use WebDevProject\Model\User;

class RegisterForm
{
    /** @var \PDO */
    private $pdo;
/** @var string[] data from $_POST */
    private $data = [];
/** @var string[] validation errors array */
    private $errors = [];
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function load(array $postData): void
    {
        $this->data['username']        = trim($postData['username']        ?? '');
        $this->data['email']           = trim($postData['email']           ?? '');
        $this->data['password']        = trim($postData['password']        ?? '');
        $this->data['password_confirm'] = trim($postData['password_confirm'] ?? '');
    }

    public function validate(): bool
    {
        $username = $this->data['username'] ?? '';
        $email    = $this->data['email'] ?? '';
        $pass     = $this->data['password'] ?? '';
        $pass2    = $this->data['password_confirm'] ?? '';
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
            if ($userModel->exists($username, $email)) {
                $this->errors[] = 'Már létezik ilyen felhasználónév vagy e-mail.';
            }
        }

        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function register(): ?int
    {
        $username = $this->data['username'];
        $email    = $this->data['email'];
        $pass     = $this->data['password'];
        $userModel = new User($this->pdo);
        return $userModel->register($username, $email, $pass);
    }

    public function getValue(string $field): string
    {
        return $this->data[$field] ?? '';
    }

    public function render(): string
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
        $html .= '<div class="form-group">';
        $html .= '  <label for="username">Felhasználónév</label>';
        $html .= '  <input'
            . ' type="text"'
            . ' class="form-control"'
            . ' id="username"'
            . ' name="username"'
            . ' placeholder="3–50 karakter"'
            . ' required minlength="3" maxlength="50"'
            . ' value="' . htmlspecialchars($this->getValue('username'), ENT_QUOTES) . '">';
        $html .= '</div>';
        $html .= '<div class="form-group">';
        $html .= '  <label for="email">E-mail cím</label>';
        $html .= '  <input'
            . ' type="email"'
            . ' class="form-control"'
            . ' id="email"'
            . ' name="email"'
            . ' placeholder="valaki@pelda.hu"'
            . ' required'
            . ' value="' . htmlspecialchars($this->getValue('email'), ENT_QUOTES) . '">';
        $html .= '</div>';
        $html .= '<div class="form-group">';
        $html .= '  <label for="password">Jelszó</label>';
        $html .= '  <input'
            . ' type="password"'
            . ' class="form-control"'
            . ' id="password"'
            . ' name="password"'
            . ' placeholder="Legalább 6 karakter"'
            . ' required minlength="6">';
        $html .= '</div>';
        $html .= '<div class="form-group">';
        $html .= '  <label for="password_confirm">Jelszó megerősítése</label>';
        $html .= '  <input'
            . ' type="password"'
            . ' class="form-control"'
            . ' id="password_confirm"'
            . ' name="password_confirm"'
            . ' placeholder="Írd be újra a jelszót"'
            . ' required minlength="6">';
        $html .= '</div>';
        $html .= '<div class="d-grid mb-3">';
        $html .= '  <button type="submit" class="btn btn-primary">';
        $html .= '    Regisztráció';
        $html .= '  </button>';
        $html .= '</div>';
        $html .= '<hr class="my-3">';
        $html .= '<p class="text-center mb-0">';
        $html .= '  Már van fiókod? <a href="login.php" class="link-info">Bejelentkezés</a>';
        $html .= '</p>';
        $html .= '</form>';
        return $html;
    }
}
