<?php

namespace WebDevProject\Form;

use PDO;
use WebDevProject\Model\User;
use WebDevProject\Security\Csrf;

class LoginForm extends BaseForm
{
    public function __construct(protected PDO $pdo)
    {
    }

    public function formLoad(array $post): void
    {
        $this->data['email'] = trim($post['email'] ?? '');
        $this->data['password'] = trim($post['password'] ?? '');
    }

    public function formValidate(): bool
    {
        if (
            $this->getValue('email') === '' || !filter_var(
                $this->getValue('email'),
                FILTER_VALIDATE_EMAIL
            )
        ) {
            $this->addError('Érvénytelen vagy hiányzó e-mail cím.');
        }
        if ($this->getValue('password') === '') {
            $this->addError('Add meg a jelszavadat.');
        }
        return !$this->hasErrors();
    }

    public function formLogin(): ?array
    {
        $user = new User($this->pdo);

        // Először ellenőrizzük, hogy a felhasználó bannolva van-e
        $stmt = $this->pdo->prepare("
            SELECT is_banned
            FROM users
            WHERE (email = :email OR username = :email) AND email_verified_at IS NOT NULL
            LIMIT 1
        ");
        $stmt->execute([':email' => $this->data['email']]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData && (int)($userData['is_banned'] ?? 0) === 1) {
            $this->addError('Ez a felhasználói fiók bannolva van. Kérjük, vegye
             fel a kapcsolatot az adminisztrátorral.');
            return null;
        }

        return $user->userLogin(
            $this->data['email'],
            $this->data['password']
        );
    }

    public function formRender(): string
    {
        $html = '';
        if ($this->hasErrors()) {
            $html .= '<div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm"
 role="alert"><ul class="mb-0">';
            foreach ($this->getErrors() as $e) {
                $html .= '<li>' . htmlspecialchars($e, ENT_QUOTES) . '</li>';
            }
            $html .= '</ul><button type="button" class="btn-close" data-bs-dismiss="alert"
 aria-label="Bezárás"></button></div>';
        }

        $html .= '<form method="post" class="container"><div class="row g-3">';

        $html .= '<div class="col-12">';
        $html .= '<input type="hidden" name="csrf" value="' . Csrf::token() . '">';
        $html .= '<label for="email" class="form-label fw-semibold text-dark">E-mail cím</label>';
        $html .= '<input type="email" name="email" id="email" class="form-control fs-5 rounded-3 bg-light"
         placeholder="E-mail cím" value="' . htmlspecialchars(
            $this->getValue('email'),
            ENT_QUOTES
        ) . '" required>';
        $html .= '</div>';

        $html .= '<div class="col-12">';
        $html .= '<label for="password" class="form-label fw-semibold text-dark">Jelszó</label>';
        $html .= '<input type="password" name="password" id="password" class="form-control fs-5 rounded-3 bg-light"
 placeholder="Jelszó" required>';
        $html .= '</div>';

        $html .= '<div class="col-12 d-grid mb-3">';
        $html .= '<button type="submit" 
class="btn btn-primary fs-5 py-2 rounded-pill shadow-sm">Bejelentkezés</button>';
        $html .= '</div>';

        $html .= '<div class="col-12"><p class="text-center mb-0">Még nincs fiókod? <a href="/register"
 class="link-success fw-semibold">Regisztráció</a></p></div>';
        $html .= '<div class="col-12"><p class="text-center mb-0">Elfelejtetted a jelszavad? <a href="/reset"
 class="link-success fw-semibold">Jelszó megváltoztatása</a></p></div>';

        $html .= '</div></form>';

        return $html;
    }
}
