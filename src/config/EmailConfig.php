<?php

namespace WebDevProject\config;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailConfig
{
    /**
     * Visszaad egy PHPMailer példányt Mailtrap SMTP beállításokkal.
     * Cseréld ki a USERNAME és PASSWORD részt a Mailtrap fiókodban található adataidra!
     *
     * @return PHPMailer
     * @throws Exception
     */
    public static function createMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = Config::MAILTRAP_HOST;
        $mail->SMTPAuth = true;
        $mail->Port       = Config::MAILTRAP_PORT;
        $mail->Username   = Config::MAILTRAP_USERNAME;
        $mail->Password   = Config::MAILTRAP_PASSWORD;
        $mail->setFrom('feri@noreply.com', 'FeriWebDevProject');
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);
        return $mail;
    }
}
