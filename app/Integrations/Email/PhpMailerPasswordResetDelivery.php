<?php
declare(strict_types=1);

namespace App\Integrations\Email;

use App\Modules\Identity\Services\PasswordResetDelivery;
use PHPMailer\PHPMailer\PHPMailer;

final class PhpMailerPasswordResetDelivery implements PasswordResetDelivery
{
    private PHPMailer $mailer;

    public function __construct(PHPMailer $mailer) { $this->mailer = $mailer; }

    public function queue(string $email, string $resetUrl): void
    {
        $mail = clone $this->mailer;
        $mail->clearAllRecipients();
        $mail->addAddress($email);
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(false);
        $mail->Subject = 'Reset your Spacivo password';
        $mail->Body = "Use this link to reset your password within 30 minutes:\n\n" . $resetUrl
            . "\n\nIf you did not request this, you can ignore this email.";
        try {
            if (!$mail->send()) {
                throw new \RuntimeException('Password recovery delivery failed.');
            }
        } catch (\Throwable $error) {
            // SMTP errors can contain credentials, addresses or recovery URLs.
            throw new \RuntimeException('Password recovery delivery failed.');
        }
    }
}
