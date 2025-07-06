<?php

declare(strict_types=1);

namespace WebDevProject\Form;

use WebDevProject\Model\User;
use WebDevProject\Security\Csrf;

class PasswordResetForm extends BaseForm
{
    public function __construct(
        protected \PDO $pdo,
        protected ?string $token = null
    ) {
    }

    public function formLoad(array $post): void
    {
        if ($this->token) {
            $this->data['password']         = trim($post['password'] ?? '');
            $this->data['password_confirm'] = trim($post['password_confirm'] ?? '');
        } else {
            $this->data['email'] = trim($post['email'] ?? '');
        }
    }

    public function formValidate(): bool
    {
        if ($this->token) {
            if ($this->getValue('password') === '' || strlen($this->getValue('password')) < 6) {
                $this->addError('A jelszó legalább 6 karakter legyen.');
            }
            if ($this->getValue('password') !== $this->getValue('password_confirm')) {
                $this->addError('A két jelszó nem egyezik.');
            }
        } else {
            if (
                $this->getValue('email') === '' || !filter_var(
                    $this->getValue('email'),
                    FILTER_VALIDATE_EMAIL
                )
            ) {
                $this->addError('Adj meg érvényes e-mail címet.');
            }
        }
        return !$this->hasErrors();
    }

    public function formSubmit(): bool
    {
        $user = new User($this->pdo);
        if ($this->token) {
            return $user->resetPassword($this->token, $this->getValue('password'));
        }
        return $user->requestPasswordReset($this->getValue('email'));
    }

    public function formRender(): string
    {
        $html = '';

        if ($this->hasErrors()) {
            $html .= '<div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">';
            $html .= '<ul class="mb-0">';
            foreach ($this->getErrors() as $error) {
                $html .= '<li>' . htmlspecialchars($error, ENT_QUOTES) . '</li>';
            }
            $html .= '</ul>';
            $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Bezárás"></button>';
            $html .= '</div>';
        }

        $html .= '<form method="post" class="container"><div class="row g-3">';
        $html .= '<div class="col-12">';
        $html .= '<input type="hidden" name="csrf" value="' . Csrf::token() . '">';
        $html .= '</div>';

        if (!$this->token) {
            $html .= '<div class="col-12">';
            $html .= '<label for="email" class="form-label fw-semibold text-dark">E-mail cím</label>';
            $html .= '<input type="email" name="email" id="email" class="form-control fs-5 rounded-3 bg-light"
             placeholder="E-mail cím" required value="' . htmlspecialchars(
                $this->getValue('email'),
                ENT_QUOTES
            ) . '">';
            $html .= '</div>';

            $html .= '<div class="col-12 d-grid mb-3">';
            $html .= '<button type="submit" class="btn btn-primary fs-5 py-2 rounded-pill shadow-sm">
            Visszaállító link küldése</button>';
            $html .= '</div>';
        } else {
            $html .= '<div class="col-12">';
            $html .= '<input type="hidden" name="token" value="' . htmlspecialchars($this->token, ENT_QUOTES) . '">';
            $html .= '</div>';

            $html .= '<div class="col-12">';
            $html .= '<label for="password" 
class="form-label fw-semibold text-dark">Új jelszó (min. 6 karakter)</label>';
            $html .= '<input type="password" name="password" id="password" 
class="form-control fs-5 rounded-3 bg-light" placeholder="Új jelszó" required minlength="6">';
            $html .= '</div>';

            $html .= '<div class="col-12">';
            $html .= '<label for="password_confirm" 
class="form-label fw-semibold text-dark">Jelszó megerősítése</label>';
            $html .= '<input type="password" name="password_confirm" id="password_confirm" 
class="form-control fs-5 rounded-3 bg-light" placeholder="Jelszó megerősítése" required minlength="6">';
            $html .= '</div>';

            $html .= '<div class="col-12 d-grid mb-3">';
            $html .= '<button type="submit" 
class="btn btn-success fs-5 py-2 rounded-pill shadow-sm">Jelszó frissítése</button>';
            $html .= '</div>';
        }

        $html .= '</div></form>';
        return $html;
    }
}
