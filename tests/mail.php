<?php
declare(strict_types=1);

use App\Integrations\Email\PasswordResetDeliveryFactory;
use App\Integrations\Email\PhpMailerPasswordResetDelivery;
use PHPMailer\PHPMailer\PHPMailer;

// Exercise PHPMailer's real MIME generation without sending to an external account.
$captured = new stdClass();
$captured->messages = [];
$captured->fail = false;
$mail = new class($captured) extends PHPMailer {
    private $captured;
    public function __construct($captured) { parent::__construct(true); $this->captured = $captured; }
    public function send() {
        if ($this->captured->fail) { throw new RuntimeException('secret SMTP password and reset token'); }
        $this->preSend();
        $this->captured->messages[] = ['mime' => $this->getSentMIMEMessage(), 'recipients' => $this->getToAddresses()];
        return true;
    }
};
$mail->setFrom('noreply@example.test', 'Spacivo');
$delivery = new PhpMailerPasswordResetDelivery($mail);
$resetUrl = 'https://example.test/?url=auth/reset_password&token=' . str_repeat('a', 64);
$delivery->queue('first@example.test', $resetUrl);
$delivery->queue('second@example.test', $resetUrl);
$check(count($captured->messages) === 2 && count($captured->messages[1]['recipients']) === 1
    && $captured->messages[1]['recipients'][0][0] === 'second@example.test', 'Mail recipients isolated between reset requests');
$mime = $captured->messages[0]['mime'];
$check(strpos($mime, $resetUrl) !== false && strpos($mime, '30 minutes') !== false
    && strpos($mime, 'Content-Type: text/plain') !== false, 'PHPMailer creates reset email with link and expiration');
$captured->fail = true;
try { $delivery->queue('first@example.test', $resetUrl); $check(false, 'Mail failure sanitized'); }
catch (RuntimeException $error) {
    $check($error->getMessage() === 'Password recovery delivery failed.' && $error->getPrevious() === null,
        'Mail failure sanitized without SMTP secrets');
}

$keys = ['APP_ENV', 'MAIL_DRIVER', 'MAIL_HOST', 'MAIL_FROM_ADDRESS', 'MAIL_PORT', 'MAIL_ENCRYPTION', 'MAIL_USERNAME', 'MAIL_PASSWORD'];
$original = [];
foreach ($keys as $key) { $original[$key] = getenv($key); }
try {
    putenv('APP_ENV=production'); putenv('MAIL_DRIVER=local');
    $check(!PasswordResetDeliveryFactory::configured(), 'Production refuses local reset outbox');
    putenv('MAIL_DRIVER=smtp'); putenv('MAIL_HOST=smtp.example.test');
    putenv('MAIL_FROM_ADDRESS=noreply@example.test'); putenv('MAIL_PORT=587');
    putenv('MAIL_ENCRYPTION=tls'); putenv('MAIL_USERNAME='); putenv('MAIL_PASSWORD=');
    $check(PasswordResetDeliveryFactory::create() instanceof PhpMailerPasswordResetDelivery, 'Production accepts configured PHPMailer SMTP');
    putenv('MAIL_ENCRYPTION=none');
    $check(!PasswordResetDeliveryFactory::configured(), 'SMTP rejects unencrypted transport');
    putenv('MAIL_ENCRYPTION=tls'); putenv('MAIL_USERNAME=account');
    $check(!PasswordResetDeliveryFactory::configured(), 'SMTP rejects incomplete authentication');
    putenv('MAIL_PASSWORD=test-only'); putenv('MAIL_HOST=smtp.example.test;attacker.example.test');
    $check(!PasswordResetDeliveryFactory::configured(), 'SMTP rejects injected host list');
} finally {
    foreach ($original as $key => $value) { putenv($value === false ? $key : $key . '=' . $value); }
}
