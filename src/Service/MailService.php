<?php

namespace src\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;
use Exception;


class MailService{
    public function sendEmailVerification(string $email, string $fullName, string $link){
        try {
            $mail = new PHPMailer(true);

            $mail->isSMTP();
            $mail->Host = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['MAIL_USER'];
            $mail->Password = $_ENV['MAIL_PASSWORD'];
            $mail->Port = $_ENV['MAIL_PORT'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

            $mail->setFrom($_ENV['MAIL_USER'], $_ENV['MAIL_FROM_NAME']);
            $mail->addAddress($email, $fullName);
            $mail->isHTML(true);
            $mail->Subject = 'Verify your email address';
            $name = htmlspecialchars($fullName, ENT_QUOTES);
            $mail->Body    = "<p>Hi {$fullName},</p>
                <p>Kindly verify your email by clicking the link below. It expires in 2 hours.</p>
                <p><a href=\"{$link}\">Verify your email</a></p>";
                $mail->AltBody = "Verify your email: {$link}";
            $mail->send();
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = 'error_log';
            return true;

        } catch (MailException $e) {
            error_log('Mail error: ' . $mail->ErrorInfo);
            return false;
        }

    }
}