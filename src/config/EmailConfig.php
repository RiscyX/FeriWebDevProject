<?php

declare(strict_types=1);

namespace WebDevProject\config;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailConfig
{
    /**
     * @return PHPMailer
     * @throws Exception
     */
    public static function createMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = Config::mailHost();
        $mail->SMTPAuth   = true;
        $mail->Port       = Config::mailPort();
        $mail->Username   = Config::mailUser();
        $mail->Password   = Config::mailPass();
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->setFrom(Config::mailFromAddress(), Config::mailFromName());
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);
        return $mail;
    }
}
