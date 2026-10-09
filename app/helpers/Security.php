<?php
declare(strict_types=1);

final class Security
{
    /**
     * Sanitizes input data to prevent XSS and other injections.
     * 
     * @param mixed $data
     * @return mixed
     */
    public static function sanitize($data)
    {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }

        if ($data === null) {
            return null;
        }

        $data = trim((string)$data);                 // remove spaces
        $data = stripslashes($data);                 // remove backslashes
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8'); // prevent XSS

        return $data;
    }
}
