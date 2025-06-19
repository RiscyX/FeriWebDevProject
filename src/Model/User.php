<?php

namespace WebDevProject\Model;

use Exception;
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

        if (! $ok) {
            return false;
        }

        $verificationLink = sprintf(
            'http://feriwebdevproject/verify?token=%s',
            urlencode($token)
        );        try {
            $mail = EmailConfig::createMailer();
        } catch (Exception $e) {
            // Log hiba részleteit
            error_log("Email küldés hiba createMailer: " . $e->getMessage());
            return false;
        }

        try {
            $mail->addAddress($email);
            $mail->Subject = 'FeriWebDev – E-mail cím megerősítése';            // Beolvassuk a HTML sablont a View/templates mappából
            $templatePath = './View/templates/verification_email.html';
            $absolutePath = realpath($templatePath);

            file_put_contents(__DIR__ . '/../../email_error.log', date('Y-m-d H:i:s') . " - Sablon relatív útvonal: " . $templatePath . "\n", FILE_APPEND);
            file_put_contents(__DIR__ . '/../../email_error.log', date('Y-m-d H:i:s') . " - Sablon abszolút útvonal: " . ($absolutePath ? $absolutePath : "Nem található") . "\n", FILE_APPEND);

            // Próbáljuk meg közvetlenül beállítani a sablon tartalmát
  

            $mail->isHTML(true);
            $mail->Body = $verificationEmailTemplate;
            file_put_contents(__DIR__ . '/../../email_error.log', date('Y-m-d H:i:s') . " - Közvetlenül beállított sablon hossza: " . strlen($verificationEmailTemplate) . " karakter\n", FILE_APPEND);

            // A file_get_contents kód a hibakeresés miatt marad itt
            if (file_exists($templatePath)) {
                try {
                    file_put_contents(__DIR__ . '/../../email_error.log', date('Y-m-d H:i:s') . " - Próbálok olvasni: " . $templatePath . "\n", FILE_APPEND);
                    $fileContent = file_get_contents($templatePath);
                    file_put_contents(__DIR__ . '/../../email_error.log', date('Y-m-d H:i:s') . " - Olvasott tartalom hossza: " . (strlen($fileContent) ?? 0) . " karakter\n", FILE_APPEND);
                } catch (\Exception $e) {
                    file_put_contents(__DIR__ . '/../../email_error.log', date('Y-m-d H:i:s') . " - Hiba a sablon betöltésekor: " . $e->getMessage() . "\n", FILE_APPEND);
                }
            } else {
                file_put_contents(__DIR__ . '/../../email_error.log', date('Y-m-d H:i:s') . " - Sablon fájl nem található: " . $templatePath . "\n", FILE_APPEND);
                // A sablon már korábban be lett állítva, nem kell itt újat beállítani
            }

            // Biztonság kedvéért ellenőrizzük, hogy a Body nem üres
            if (empty($mail->Body)) {
                file_put_contents(__DIR__ . '/../../email_error.log', date('Y-m-d H:i:s') . " - FIGYELEM: Email body üres maradt a beállítás után!\n", FILE_APPEND);
                $mail->Body = "Kérlek, erősítsd meg az e-mail címedet az alábbi linkre kattintva: {$verificationLink}";
            }

            $mail->AltBody = "Üdvözlünk az FeriWebDev-nél!\n\n"
                . "Kérlek, másold be ezt a böngészőbe a megerősítéshez:\n"
                . "{$verificationLink}\n\n"
                . "Ha nem Te regisztráltál, hagyd figyelmen kívül ezt az üzenetet.\n";
        } catch (Exception $e) {
            error_log("Email küldés hiba beállítás: " . $e->getMessage());
            return false;
        }        try {
            $result = $mail->send();
            error_log("Email küldés eredménye: " . ($result ? "Sikeres" : "Sikertelen"));
            return $result;
        } catch (Exception $e) {
            // Részletes hibaüzenet logolása
            error_log("Email küldés hiba: " . $e->getMessage());
            // File_put_contents is jó lehet a hibakereséshez
            file_put_contents(__DIR__ . '/../../email_error.log', date('Y-m-d H:i:s') . " - Email küldési hiba: " . $e->getMessage() . "\n", FILE_APPEND);
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
            $mail->Body = "
              <p>Szia!</p>
              <p>A jelszó visszaállításához kattints:<br>
                 <a href=\"{$resetLink}\">{$resetLink}</a></p>
              <p>Ha nem te kérted, hagyd figyelmen kívül.</p>";
            $mail->AltBody = "Jelszó-visszaállítás: {$resetLink}";
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
}
