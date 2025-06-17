<?php

namespace WebDevProject\Form;

use WebDevProject\Model\User;

class LoginForm
{
    private array $data  = [];
    private array $errors = [];

    public function __construct(
        private \PDO $pdo
    ) {
    }

    public function formLoad(array $post): void
    {
        $this->data['email']    = trim($post['email']    ?? '');
        $this->data['password'] = trim($post['password'] ?? '');
    }

    public function formValidate(): bool
    {
        if ($this->data['email'] === '' || !filter_var($this->data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'Érvénytelen vagy hiányzó e-mail cím.';
        }
        if ($this->data['password'] === '') {
            $this->errors[] = 'Add meg a jelszavadat.';
        }
        return empty($this->errors);
    }

    public function formLogin(): ?array
    {
        $user = new User($this->pdo);
        return $user->userLogin($this->data['email'], $this->data['password']);
    }

    public function &getErrors(): array
    {
        return $this->errors;
    }

    public function formRender(): string
    {
        $html = '';
        if ($this->errors) {
            $html .= '<div class="alert alert-danger"><ul class="mb-0">';
            foreach ($this->errors as $e) {
                $html .= '<li>' . htmlspecialchars($e, ENT_QUOTES) . '</li>';
            }
            $html .= '</ul></div>';
        }

        $html .= '<form method="post" class="d-grid gap-3">';
        // E-mail
        $html .= '<div class="form-floating">';
        $html .= '  <input'
            . ' type="email"'
            . ' name="email"'
            . ' class="form-control fs-5"'
            . ' id="email"'
            . ' placeholder="E-mail cím"'
            . ' value="' . htmlspecialchars($this->data['email'] ?? '', ENT_QUOTES) . '"'
            . ' required>';
        $html .= '  <label for="email">E-mail cím</label>';
        $html .= '</div>';
        // Jelszó
        $html .= '<div class="form-floating">';
        $html .= '  <input'
            . ' type="password"'
            . ' name="password"'
            . ' class="form-control fs-5"'
            . ' id="password"'
            . ' placeholder="Jelszó"'
            . ' required>';
        $html .= '  <label for="password">Jelszó</label>';
        $html .= '</div>';
        // Submit
        $html .= '<div class="d-grid mb-3">';
        $html .= '  <button type="submit" class="btn btn-primary fs-5">Bejelentkezés</button>';
        $html .= '</div>';
        $html .= '<p class="text-center mb-0">';
        $html .= '  Még nincs fiókod? <a href="/register" class="link-info">Regisztráció</a>';
        $html .= '</p>';
        $html .= '</form>';

        return $html;
    }
}
