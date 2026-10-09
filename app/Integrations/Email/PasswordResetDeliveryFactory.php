<?php
declare(strict_types=1);

namespace App\Integrations\Email;

use App\Modules\Identity\Services\PasswordResetDelivery;
use PHPMailer\PHPMailer\PHPMailer;

final class PasswordResetDeliveryFactory
{
    public static function create(): PasswordResetDelivery
    {
        $environment = getenv('APP_ENV');
        $driver = getenv('MAIL_DRIVER') ?: 'local';
        if ($driver === 'local' && in_array($environment, ['local', 'testing'], true)) {
            return new LocalPasswordResetDelivery(getenv('PASSWORD_RESET_OUTBOX')
                ?: dirname(__DIR__, 3) . '/storage/private/password-reset-outbox');
        }
        if ($driver !== 'smtp') {
            throw new \RuntimeException('Password recovery delivery is not configured.');
        }
        $host = getenv('MAIL_HOST') ?: '';
        $from = getenv('MAIL_FROM_ADDRESS') ?: '';
        $port = getenv('MAIL_PORT') ?: '587';
        $encryption = getenv('MAIL_ENCRYPTION') ?: 'tls';
        if (!preg_match('/^[a-zA-Z0-9.-]+$/D', $host) || !filter_var($from, FILTER_VALIDATE_EMAIL)
            || !ctype_digit($port) || (int)$port < 1 || (int)$port > 65535
            || !in_array($encryption, ['tls', 'ssl'], true) || !extension_loaded('openssl')) {
            throw new \RuntimeException('Password recovery SMTP configuration is invalid.');
        }
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->Port = (int)$port;
        $mail->SMTPSecure = $encryption === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Username = getenv('MAIL_USERNAME') ?: '';
        $mail->Password = getenv('MAIL_PASSWORD') ?: '';
        $mail->SMTPAuth = $mail->Username !== '';
        if ($mail->SMTPAuth && $mail->Password === '') {
            throw new \RuntimeException('Password recovery SMTP credentials are incomplete.');
        }
        $mail->SMTPDebug = 0;
        $mail->Timeout = 10;
        $mail->setFrom($from, getenv('MAIL_FROM_NAME') ?: 'Spacivo');
        return new PhpMailerPasswordResetDelivery($mail);
    }

    public static function configured(): bool
    {
        try { self::create(); return true; }
        catch (\Throwable $error) { return false; }
    }
}
