<?php
declare(strict_types=1);
namespace App\Shared\Security;

final class PermissionSeeder
{
    public static function seed(\mysqli $db): int
    {
        $catalog = require __DIR__ . '/../../../config/management_permissions.php';
        $definitions = json_decode(file_get_contents(__DIR__ . '/../../../database/seeds/permissions.json'), true, 512, JSON_THROW_ON_ERROR);
        $bySlug = array_column($definitions, null, 'slug');
        $slugs = array_unique(array_merge(array_keys($bySlug), ...array_values($catalog)));
        $added = 0;
        $db->begin_transaction();
        try {
            $stmt = $db->prepare('INSERT INTO permissions (name, slug, category, module, description) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE slug = VALUES(slug)');
            foreach ($slugs as $slug) {
                $name = $bySlug[$slug]['name'] ?? ucwords(str_replace('_', ' ', $slug));
                $module = ucwords(str_replace('_', ' ', preg_replace('/^(view|add|edit|delete|manage|print|export)_/', '', $slug)));
                $category = $bySlug[$slug]['category'] ?? 'Management Actions';
                $module = $bySlug[$slug]['module'] ?? $module;
                $description = $bySlug[$slug]['description'] ?? ('Authorizes the ' . $name . ' action; ownership and organization boundaries still apply.');
                $stmt->bind_param('sssss', $name, $slug, $category, $module, $description);
                $stmt->execute();
                if ($stmt->affected_rows === 1) $added++;
            }
            $db->commit();
            return $added;
        } catch (\Throwable $error) {
            $db->rollback();
            throw $error;
        }
    }
}
