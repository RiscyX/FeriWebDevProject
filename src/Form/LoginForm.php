<?php

namespace WebDevProject\Form;

use WebDevProject\Model\User;

class LoginForm
{
    private array $data = [];
    private array $errors = [];

    public function __construct(
        protected \PDO $pdo
    ) {
    }

    public function formLoad(array $post): void
    {
        $this->data['email'] = trim($post['email'] ?? '');
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
            $html .= '<div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">';
            $html .= '<ul class="mb-0">';
            foreach ($this->errors as $e) {
                $html .= '<li>' . htmlspecialchars($e, ENT_QUOTES) . '</li>';
            }
            $html .= '</ul>';
            $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Bezárás"></button>';
            $html .= '</div>';
        }

        $html .= '<form method="post" class="container" >';
        $html .= '<div class="row g-3">';
        // E-mail
        $html .= '<div class="col-12">';
        $html .= '  <label for="email" class="form-label fw-semibold text-dark">E-mail cím</label>';
        $html .= '  <input'
            . ' type="email"'
            . ' name="email"'
            . ' class="form-control fs-5 rounded-3 bg-light"'
            . ' id="email"'
            . ' placeholder="E-mail cím"'
            . ' value="' . htmlspecialchars($this->data['email'] ?? '', ENT_QUOTES) . '"'
            . ' required>';
        $html .= '</div>';
        // Jelszó
        $html .= '<div class="col-12">';
        $html .= '  <label for="password" class="form-label fw-semibold text-dark">Jelszó</label>';
        $html .= '  <input'
            . ' type="password"'
            . ' name="password"'
            . ' class="form-control fs-5 rounded-3 bg-light"'
            . ' id="password"'
            . ' placeholder="Jelszó"'
            . ' required>';
        $html .= '</div>';
        // Submit
        $html .= '<div class="col-12 d-grid mb-3">';
        $html .= '<button type="submit" 
                  class="btn btn-primary fs-5 py-2 rounded-pill shadow-sm">
                    Bejelentkezés
                  </button>';
        $html .= '</div>';
        $html .= '<div class="col-12">';
        $html .= '<p class="text-center mb-0">Még nincs fiókod? 
                    <a href="/register" class="link-success fw-semibold">
                        Regisztráció
                   </a></p>';
        $html .= '</div>';
        $html .= '<div class="col-12">';
        $html .= '<p class="text-center mb-0">Elfelejtetted a jelszavad? 
                    <a href="/reset" class="link-success fw-semibold">
                        Jelszó megváltoztatása
                   </a></p>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</form>';

        return $html;
    }
}
