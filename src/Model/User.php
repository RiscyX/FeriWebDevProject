<?php

namespace WebDevProject\Model;

use Exception;
use PDO;
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
        return (bool)$stmt->fetch();
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

        if (!$ok) {
            return null;
        }

        return (int)$this->pdo->lastInsertId();
    }

    public function userLogin(string $email, string $plainPassword): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, username, email, password_hash, role, is_banned
            FROM users 
            WHERE (email = :x OR username = :x) AND email_verified_at is not null 
            LIMIT 1
        ");
        $stmt->execute([':x' => $email]);
        $user = $stmt->fetch();

        // Ellenőrizzük, hogy a felhasználó nincs-e bannolva
        if ($user && (int)$user['is_banned'] === 1) {
            return null; // Bannolt felhasználó nem jelentkezhet be
        }

        if ($user && password_verify($plainPassword, $user['password_hash'])) {
            return [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
            ];
        }
        return null;
    }

    /**
     * @param int $userId
     * @param string $email
     * @return bool
     * @throws \PHPMailer\PHPMailer\Exception
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

        if (!$ok) {
            return false;
        }

        $verificationLink = sprintf(
            'http://feriwebdevproject/verify?token=%s',
            urlencode($token)
        );
        try {
            $mail = EmailConfig::createMailer();
        } catch (Exception $e) {
            error_log("Email küldés hiba createMailer: " . $e->getMessage());
            return false;
        }

        try {
            $mail->addAddress($email);
            $mail->Subject = 'FeriWebDev – E-mail cím megerősítése';
            $mail->isHTML(true);
            $templatePath = __DIR__ . '/../View/templates/verification_email.html';
            $bodyTpl = file_get_contents($templatePath);
            $bodyHtml = str_replace('[verification_link]', $verificationLink, $bodyTpl);
            $mail->Body = $bodyHtml;
            $mail->AltBody = "Üdvözlünk az FeriWebDev-nél!\n\n"
                . "Kérlek, másold be ezt a böngészőbe a megerősítéshez:\n"
                . "{$verificationLink}\n\n"
                . "Ha nem Te regisztráltál, hagyd figyelmen kívül ezt az üzenetet.\n";
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email küldés hiba beállítás: " . $e->getMessage());
            return false;
        }
    }

    public function requestPasswordReset(string $email): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT id FROM users WHERE email = :e LIMIT 1"
        );
        $stmt->execute([':e' => $email]);
        $userId = $stmt->fetchColumn();

        if (!$userId) {
            return true;
        }

        $token = bin2hex(random_bytes(32));

        $ok = $this->pdo->prepare(
            "INSERT INTO password_resets (user_id, token, created_at)
                   VALUES (:u, :t, NOW())"
        )->execute([':u' => $userId, ':t' => $token]);

        if (!$ok) {
            return false;
        }

        $base = $_ENV['APP_URL']
            ?? ((PHP_SAPI === 'cli')
                ? 'localhost/FeriWebDevProject/'
                : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http')
                . '://' . ($_SERVER['HTTP_HOST'] ?? 'feriwebdevproject'));

        $resetLink = $base . '/reset?token=' . urlencode($token);

        try {
            $mail = EmailConfig::createMailer();
            $mail->addAddress($email);
            $mail->Subject = 'FeriWebDev – Jelszó-visszaállítás';
            $templatePath = __DIR__ . '/../View/templates/reset_email.html';
            $bodyTpl = file_get_contents($templatePath);
            $bodyHtml = str_replace('[reset_link]', $resetLink, $bodyTpl);
            $mail->Body = $bodyHtml;
            $mail->AltBody = "
              <p>Szia!</p>
              <p>A jelszó visszaállításához kattints:<br>
                 <a href=\"{$resetLink}\">{$resetLink}</a></p>
              <p>Ha nem te kérted, hagyd figyelmen kívül.</p>";
            $mail->send();
            return true;
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            error_log('[MAILERR] ' . $e->getMessage());
            return false;
        }
    }

    public function resetPassword(string $token, string $newPwd): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT user_id
               FROM password_resets
              WHERE token = :t
                AND created_at >= (NOW() - INTERVAL 10 MINUTE)
              LIMIT 1"
        );
        $stmt->execute([':t' => $token]);
        $userId = $stmt->fetchColumn();

        if (!$userId) {
            return false;
        }

        $pwdHash = password_hash($newPwd, PASSWORD_DEFAULT);

        $ok = $this->pdo->prepare(
            "UPDATE users SET password_hash = :p WHERE id = :u LIMIT 1"
        )->execute([':p' => $pwdHash, ':u' => $userId]);

        if ($ok) {
            $this->pdo->prepare("DELETE FROM password_resets WHERE token = :t")
                ->execute([':t' => $token]);
        }
        return $ok;
    }

    /**
     * Felhasználó bannolása
     *
     * @param PDO $pdo
     * @param int $id Felhasználó azonosítója
     * @return bool Sikeres volt-e a művelet
     */
    public static function ban(PDO $pdo, int $id): bool
    {
        $sql = 'UPDATE users SET is_banned = 1 WHERE id = :id LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Felhasználó bannolásának feloldása
     *
     * @param PDO $pdo
     * @param int $id Felhasználó azonosítója
     * @return bool Sikeres volt-e a művelet
     */
    public static function unban(PDO $pdo, int $id): bool
    {
        $sql = 'UPDATE users SET is_banned = 0 WHERE id = :id LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public static function count(PDO $pdo): int
    {
        $sql  = 'SELECT COUNT(*) FROM users
             WHERE email_verified_at IS NOT NULL';

        return (int) $pdo->query($sql)->fetchColumn();
    }

    /**
     * Lapozott lekérdezés – csak a verifikált fiókok.
     *
     * @return array<array{
     *     id:int, username:string, email:string, role:string,
     *     created_at:string, is_banned:int}>
     */
    public static function paginated(PDO $pdo, int $limit, int $offset): array
    {
        $sql = 'SELECT id, username, email, role, created_at, is_banned
            FROM users
            WHERE email_verified_at IS NOT NULL
            ORDER BY id DESC
            LIMIT :l OFFSET :o';

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':l', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':o', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
