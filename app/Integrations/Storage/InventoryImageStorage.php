<?php
declare(strict_types=1);

namespace App\Integrations\Storage;

final class InventoryImageStorage
{
    private string $directory;
    public function __construct(string $directory) { $this->directory = rtrim($directory, '/\\'); }
    public function storeUploaded(array $file): string
    {
        if (($file['error'] ?? null) !== UPLOAD_ERR_OK || !is_string($file['tmp_name'] ?? null)
            || !is_uploaded_file($file['tmp_name']) || !extension_loaded('gd')) {
            throw new \InvalidArgumentException('Upload one supported photo.');
        }
        $path = $file['tmp_name'];
        $size = filesize($path);
        $details = @getimagesize($path);
        $extensions = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
        if ($size === false || $size < 1 || $size > 5 * 1024 * 1024 || !$details
            || !isset($extensions[$details[2]]) || $details[0] < 1 || $details[1] < 1
            || $details[0] > 6000 || $details[1] > 6000 || $details[0] * $details[1] > 12000000) {
            throw new \InvalidArgumentException('Use a JPEG, PNG or WebP photo up to 5 MB and 12 megapixels.');
        }
        $pixels = @imagecreatefromstring(file_get_contents($path));
        if ($pixels === false) { throw new \InvalidArgumentException('The photo could not be read.'); }
        if (!is_dir($this->directory) && !mkdir($this->directory, 0700, true) && !is_dir($this->directory)) {
            imagedestroy($pixels); throw new \RuntimeException('Photo storage is unavailable.');
        }
        $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$details[2]];
        $target = $this->directory . '/' . $filename;
        try {
            // Re-encode raster pixels to remove metadata and embedded executable payloads.
            if ($details[2] === IMAGETYPE_JPEG) { $written = imagejpeg($pixels, $target, 85); }
            elseif ($details[2] === IMAGETYPE_PNG) { imagesavealpha($pixels, true); $written = imagepng($pixels, $target, 6); }
            else { $written = imagewebp($pixels, $target, 85); }
            if (!$written || filesize($target) > 5 * 1024 * 1024) { throw new \InvalidArgumentException('The processed photo exceeds the supported size.'); }
            chmod($target, 0600); return $filename;
        } catch (\Throwable $error) {
            if (is_file($target)) { unlink($target); } throw $error;
        } finally { imagedestroy($pixels); }
    }
    public function path(string $filename): string
    {
        if (!preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/D', $filename)) { throw new \InvalidArgumentException('Invalid photo reference.'); }
        return $this->directory . '/' . $filename;
    }
    public function remove(string $filename): void
    {
        $path = $this->path($filename); if (is_file($path)) { unlink($path); }
    }
}
