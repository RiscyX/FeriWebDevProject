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
        private \PDO $pdo,
        /** A token a reset.php?token=... URL-paraméterből érkezik. */
        private ?string $token = null
    ) {
    }

    /*==== 1)  LOAD =======================================================*/
    public function formLoad(array $post): void
    {
        if ($this->token) {
            // Jelszó-visszaállító űrlap
            $this->data['password']         = trim($post['password']         ?? '');
            $this->data['password_confirm'] = trim($post['password_confirm'] ?? '');
        } else {
            // Linkkérő űrlap
            $this->data['email'] = trim($post['email'] ?? '');
        }
    }

    /*==== 2)  VALIDATE ===================================================*/
    public function formValidate(): bool
    {
        if ($this->token) {
            // --- Új jelszó ellenőrzése ---
            $pass1 = $this->data['password']         ?? '';
            $pass2 = $this->data['password_confirm'] ?? '';

            if ($pass1 === '' || strlen($pass1) < 6) {
                $this->errors[] = 'A jelszó legalább 6 karakter legyen.';
            }
            if ($pass1 !== $pass2) {
                $this->errors[] = 'A két jelszó nem egyezik.';
            }
        } else {
            // --- E-mail ellenőrzése ---
            $email = $this->data['email'] ?? '';
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->errors[] = 'Adj meg érvényes e-mail címet.';
            }
        }
        return empty($this->errors);
    }

    /*==== 3)  AKCIÓ ======================================================*/
    /**
     *  Ha nincs token → visszaállító link küldése
     *  Ha van token   → tényleges jelszócsere
     * @throws RandomException
     */
    public function formSubmit(): bool
    {
        $user = new User($this->pdo);

        if ($this->token) {
            // <<< JELSZÓ FRISSÍTÉS >>>
            return $user->sendPasswordReset($this->token, $this->data['password']);
        }
        // <<< LINK KÜLDÉSE >>>
        return $user->sendPasswordReset($this->data['email']);
    }

    public function &formGetErrors(): array
    {
        return $this->errors;
    }

    public function formGetValue(string $field): string
    {
        return $this->data[$field] ?? '';
    }

    /*==== 4)  RENDER =====================================================*/
    public function formRender(): string
    {
        $html = '';

        /*-- hibák --*/
        if ($this->errors) {
            $html .= '<div class="alert alert-danger"><ul class="mb-0">';
            foreach ($this->errors as $e) {
                $html .= '<li>' . htmlspecialchars($e, ENT_QUOTES) . '</li>';
            }
            $html .= '</ul></div>';
        }

        $action = $this->token ? 'reset.php?token=' . urlencode($this->token)
            : 'forgot.php';

        $html .= '<form action="' . $action . '" method="post" class="d-grid gap-3">';

        /*------ 1)  TOKEN NÉLKÜL: E-MAIL MEZŐ ------*/
        if (!$this->token) {
            $html .= '<div class="form-floating">';
            $html .= '  <input type="email" name="email" id="email"'
                . ' class="form-control fs-5" placeholder="E-mail" required'
                . ' value="' . htmlspecialchars($this->formGetValue('email'), ENT_QUOTES) . '">';
            $html .= '  <label for="email">E-mail cím</label>';
            $html .= '</div>';

            $html .= '<button class="btn btn-primary fs-5">Visszaállító link küldése</button>';
        }

        /*------ 2)  TOKENNEL: ÚJ JELSZÓ MEZŐK ------*/
        if ($this->token) {
            // elrejtem a tokent is (ha POST-ban szeretnéd újra elküldeni)
            $html .= '<input type="hidden" name="token" value="' . htmlspecialchars($this->token, ENT_QUOTES) . '">';

            // Új jelszó
            $html .= '<div class="form-floating">';
            $html .= '  <input type="password" name="password" id="password"'
                . ' class="form-control fs-5" placeholder="Új jelszó" required minlength="6">';
            $html .= '  <label for="password">Új jelszó (min. 6 karakter)</label>';
            $html .= '</div>';

            // Megerősítés
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
