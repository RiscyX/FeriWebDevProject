<?php

declare(strict_types=1);

namespace WebDevProject\Form;

use Random\RandomException;
use WebDevProject\Model\User;
use WebDevProject\Security\Csrf;

class RegisterForm extends BaseForm
{
    public function __construct(protected \PDO $pdo)
    {
    }

    /**
     * @param array $post
     * @return void
     */
    public function formLoad(array $post): void
    {
        $this->data['username']         = trim($post['username'] ?? '');
        $this->data['email']            = trim($post['email'] ?? '');
        $this->data['password']         = trim($post['password'] ?? '');
        $this->data['password_confirm'] = trim($post['password_confirm'] ?? '');
    }

    /**
     * @return bool
     */
    public function formValidate(): bool
    {
        if (
            $this->getValue('username') === '' || strlen($this->getValue('username')) < 3
            || strlen($this->getValue('username')) > 50
        ) {
            $this->addError('A felhasználónév 3 és 50 karakter között kell legyen.');
        }
        if (
            $this->getValue('email') === '' || !filter_var(
                $this->getValue('email'),
                FILTER_VALIDATE_EMAIL
            )
        ) {
            $this->addError('Érvénytelen vagy hiányzó e-mail cím.');
        }
        if ($this->getValue('password') === '' || strlen($this->getValue('password')) < 6) {
            $this->addError('A jelszó legalább 6 karakter legyen.');
        }
        if ($this->getValue('password') !== $this->getValue('password_confirm')) {
            $this->addError('A jelszavak nem egyeznek.');
        }

        if (!$this->hasErrors()) {
            $userModel = new User($this->pdo);
            if ($userModel->userExists($this->getValue('username'), $this->getValue('email'))) {
                $this->addError('Már létezik ilyen felhasználónév vagy e-mail.');
            }
        }

        return !$this->hasErrors();
    }

    /**
     * @return int|null
     */
    public function formRegister(): ?int
    {
        return (new User($this->pdo))->userRegister(
            $this->getValue('username'),
            $this->getValue('email'),
            $this->getValue('password')
        );
    }

    /**
     * @return string
     * @throws RandomException
     */
    public function formRender(): string
    {
        $html = '';

        if ($this->hasErrors()) {
            $html .= '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
            $html .= '<ul class="mb-0">';
            foreach ($this->getErrors() as $err) {
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
        $html .= '<input type="hidden" name="csrf" value="' . Csrf::token() . '">';

        $html .= '<div class="col-12">';
        $html .= '<label for="username" class="form-label fw-semibold text-dark">Felhasználónév</label>';
        $html .= '<input type="text" name="username" id="username" class="form-control fs-5 rounded-3 bg-light"
         placeholder="Felhasználónév" required minlength="3" maxlength="50" 
         value="' . htmlspecialchars($this->getValue('username'), ENT_QUOTES) . '">';
        $html .= '</div>';

        $html .= '<div class="col-12">';
        $html .= '<label for="email" class="form-label fw-semibold text-dark">E-mail cím</label>';
        $html .= '<input type="email" name="email" id="email" class="form-control fs-5 rounded-3 bg-light"
         placeholder="E-mail cím" required value="' . htmlspecialchars(
            $this->getValue('email'),
            ENT_QUOTES
        ) . '">';
        $html .= '</div>';

        $html .= '<div class="col-12">';
        $html .= '<label for="password" class="form-label fw-semibold text-dark">Jelszó</label>';
        $html .= '<input type="password" name="password" id="password" class="form-control fs-5 rounded-3 bg-light"
 placeholder="Jelszó" required>';
        $html .= '</div>';

        $html .= '<div class="col-12">';
        $html .= '<label for="password_confirm" class="form-label fw-semibold text-dark">Jelszó megerősítése</label>';
        $html .= '<input type="password" name="password_confirm" id="password_confirm" 
class="form-control fs-5 rounded-3 bg-light" placeholder="Jelszó megerősítése" required>';
        $html .= '</div>';

        $html .= '<div class="col-12 d-grid mb-3">';
        $html .= '<button type="submit" class="btn btn-primary fs-5 py-2 rounded-pill shadow-sm">Regisztráció</button>';
        $html .= '</div>';

        $html .= '<div class="col-12">';
        $html .= '<p class="text-center mb-0">Már van fiókod? <a href="/login"
 class="link-success fw-semibold">Bejelentkezés</a></p>';
        $html .= '</div>';

        $html .= '</div></form>';

        return $html;
    }
}
