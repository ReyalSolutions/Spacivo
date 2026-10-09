<?php
declare(strict_types=1);

class SystemSetting
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    /**
     * Get a setting value by key.
     */
    public function get(string $key, $default = null): ?string
    {
        $stmt = $this->db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        return $row ? $row['setting_value'] : $default;
    }

    /**
     * Set a setting value by key.
     */
    public function set(string $key, ?string $value): bool
    {
        $stmt = $this->db->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param("sss", $key, $value, $value);
        return $stmt->execute();
    }

    /**
     * Get all settings as an associative array.
     */
    public function getAll(): array
    {
        $result = $this->db->query("SELECT setting_key, setting_value FROM system_settings");
        $settings = [];
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    /**
     * Batch save settings.
     */
    public function saveBatch(array $data): bool
    {
        $this->db->begin_transaction();
        try {
            foreach ($data as $key => $value) {
                $this->set((string)$key, (string)$value);
            }
            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollback();
            return false;
        }
    }
}
