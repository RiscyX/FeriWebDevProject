<?php

// src/Form/PasswordResetForm.php
namespace WebDevProject\Form;

use Random\RandomException;
use WebDevProject\Model\User;

class PasswordResetForm
{
    /** @var string[] űrlap-adatok (POST) */
    private array $data = [];
    /** @var string[] validációs hibák */
    private array $errors = [];

    public function __construct(
        protected \PDO $pdo,
        protected ?string $token = null
    ) {
    }

    public function formLoad(array $post): void
    {
        if ($this->token) {
            $this->data['password']         = trim($post['password']         ?? '');
            $this->data['password_confirm'] = trim($post['password_confirm'] ?? '');
        } else {
            $this->data['email'] = trim($post['email'] ?? '');
        }
    }

    public function formValidate(): bool
    {
        if ($this->token) {
            $pass1 = $this->data['password']         ?? '';
            $pass2 = $this->data['password_confirm'] ?? '';

            if ($pass1 === '' || strlen($pass1) < 6) {
                $this->errors[] = 'A jelszó legalább 6 karakter legyen.';
            }
            if ($pass1 !== $pass2) {
                $this->errors[] = 'A két jelszó nem egyezik.';
            }
        } else {
            $email = $this->data['email'] ?? '';
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->errors[] = 'Adj meg érvényes e-mail címet.';
            }
        }
        return empty($this->errors);
    }

    public function formSubmit(): bool
    {
        $user = new User($this->pdo);

        if ($this->token) {
            return $user->resetPassword($this->token, $this->data['password']);
        }

        return $user->requestPasswordReset($this->data['email']);
    }

    public function formGetValue(string $field): string
    {
        return $this->data[$field] ?? '';
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

        $html .= '<form action=" " method="post" class="d-grid gap-3">';

        if (!$this->token) {
            $html .= '<div class="form-floating">';
            $html .= '  <input type="email" name="email" id="email"'
                . ' class="form-control fs-5" placeholder="E-mail" required'
                . ' value="' . htmlspecialchars($this->formGetValue('email'), ENT_QUOTES) . '">';
            $html .= '  <label for="email">E-mail cím</label>';
            $html .= '</div>';

            $html .= '<button class="btn btn-primary fs-5">Visszaállító link küldése</button>';
        }

        if ($this->token) {
            $html .= '<input type="hidden" name="token" value="' . htmlspecialchars($this->token, ENT_QUOTES) . '">';

            $html .= '<div class="form-floating">';
            $html .= '  <input type="password" name="password" id="password"'
                . ' class="form-control fs-5" placeholder="Új jelszó" required minlength="6">';
            $html .= '  <label for="password">Új jelszó (min. 6 karakter)</label>';
            $html .= '</div>';

            $html .= '<div class="form-floating">';
            $html .= '  <input type="password" name="password_confirm" id="password_confirm"'
                . ' class="form-control fs-5" placeholder="Jelszó megerősítése" required minlength="6">';
            $html .= '  <label for="password_confirm">Jelszó megerősítése</label>';
            $html .= '</div>';

            $html .= '<button class="btn btn-success fs-5">Jelszó frissítése</button>';
        }

        $html .= '</form>';
        return $html;
    }
}
