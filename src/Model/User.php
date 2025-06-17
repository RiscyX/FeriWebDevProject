<?php

namespace WebDevProject\Model;

use Exception;
use Random\RandomException;
use WebDevProject\config\EmailConfig;

class User
{
    public function __construct(
        protected \PDO $pdo
    ) {
    }

    public function userExists(string $username, string $email): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT id 
            FROM users 
            WHERE username = :u OR email = :e 
            LIMIT 1
        ");
        $stmt->execute([
            ':u' => $username,
            ':e' => $email,
        ]);
        return (bool) $stmt->fetch();
    }

    public function userRegister(string $username, string $email, string $plainPassword): ?int
    {
        $hash = password_hash($plainPassword, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare("
            INSERT INTO users (username, email, password_hash)
            VALUES (:u, :e, :p)
        ");
        $ok = $stmt->execute([
            ':u' => $username,
            ':e' => $email,
            ':p' => $hash,
        ]);

        if (! $ok) {
            return null;
        }

        return (int)$this->pdo->lastInsertId();
    }

    public function userLogin(string $email, string $plainPassword): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, username, email, password_hash 
            FROM users 
            WHERE email = :x OR username = :x 
            LIMIT 1
        ");
        $stmt->execute([':x' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($plainPassword, $user['password_hash'])) {
            return [
                'id'       => $user['id'],
                'username' => $user['username'],
                'email'    => $user['email'],
            ];
        }
        return null;
    }

     /**
     * @param int    $userId
     * @param string $email
     * @return bool
     */
    public function sendVerification(int $userId, string $email): bool
    {
        try {
            $token = bin2hex(random_bytes(32));
        } catch (\Exception $e) {
            return false;
        }

        $insert = $this->pdo->prepare("
            INSERT INTO email_verifications (user_id, token, created_at)
            VALUES (:u, :t, NOW())
        ");
        $ok = $insert->execute([
            ':u' => $userId,
            ':t' => $token,
        ]);

        if (! $ok) {
            return false;
        }

        $verificationLink = sprintf(
            'https://localhost/FeriWebDevProject/public_html/verify.php?token=%s',
            urlencode($token)
        );

        try {
            $mail = EmailConfig::createMailer();
        } catch (Exception $e) {
            return false;
        }

        $mail->addAddress($email);
        $mail->Subject = 'FeriWebDev – E-mail cím megerősítése';
        $mail->Body = "
            <p>Üdvözlünk az FeriWebDev-nél!</p>
            <p>Kérlek, erősítsd meg az e-mail címedet az alábbi linkre kattintva:</p>
            <p><a href=\"{$verificationLink}\">{$verificationLink}</a></p>
            <p>Ha nem Te regisztráltál erre az e-mail címre, hagyd figyelmen kívül ezt az üzenetet.</p>
            <br>
            <p>Üdvözlettel,<br>Az FeriWebDev csapata</p>
        ";
        $mail->AltBody = "Üdvözlünk az FeriWebDev-nél!\n\n"
            . "Kérlek, másold be ezt a böngészőbe a megerősítéshez:\n"
            . "{$verificationLink}\n\n"
            . "Ha nem Te regisztráltál, hagyd figyelmen kívül ezt az üzenetet.\n";

        try {
            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getUserByEmail(string $email): int
    {
        $stmt = $this->pdo->prepare("
        SELECT id FROM users WHERE email = :e OR username = :e LIMIT 1
    ");
        $stmt->execute([':e' => $email]);
        return ($row = $stmt->fetch()) ? (int)$row['id'] : -1;
    }

    /**
     * @throws RandomException
     */
    public function sendPasswordReset(string $email): bool
    {
        $user =  $this->getUserByEmail($email);

        if (! $user) {
            return true;
        }
        $token = bin2hex(random_bytes(32));

        $ins = $this->pdo->prepare("
        INSERT INTO password_resets (user_id, token, created_at)
        VALUES (:u, :t, NOW())
    ");
        if (! $ins->execute([':u' => $user, ':t' => $token])) {
            return false;
        }

        // 4) E-mail küldés
        $resetLink = sprintf(
            'https://localhost/FeriWebDevProject/public_html/reset.php?token=%s',
            urlencode($token)
        );

        try {
            $mail = EmailConfig::createMailer();
            $mail->addAddress($email);
            $mail->Subject = 'FeriWebDev – Jelszó-visszaállítás';
            $mail->Body = "
            <p>Szia!</p>
            <p>A jelszó visszaállításához kattints az alábbi linkre:</p>
            <p><a href=\"{$resetLink}\">{$resetLink}</a></p>
            <p>Ha nem te kérted, hagyd figyelmen kívül.</p>
        ";
            $mail->AltBody = "Jelszó-visszaállítás: {$resetLink}";
            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
