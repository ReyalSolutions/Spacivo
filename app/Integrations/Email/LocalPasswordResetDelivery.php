<?php
declare(strict_types=1);

namespace App\Integrations\Email;

use App\Modules\Identity\Services\PasswordResetDelivery;

final class LocalPasswordResetDelivery implements PasswordResetDelivery
{
    private string $directory;
    public function __construct(string $directory) { $this->directory = $directory; }
    public function queue(string $email, string $resetUrl): void
    {
        if (!is_dir($this->directory) && !mkdir($this->directory, 0700, true) && !is_dir($this->directory)) {
            throw new \RuntimeException('Unable to initialize local recovery outbox.');
        }
        $path = $this->directory . '/' . bin2hex(random_bytes(16)) . '.json';
        $record = json_encode(['recipient' => $email, 'reset_url' => $resetUrl], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        if (file_put_contents($path, $record, LOCK_EX) === false) {
            throw new \RuntimeException('Unable to queue recovery instructions.');
        }
        chmod($path, 0600);
    }
}
