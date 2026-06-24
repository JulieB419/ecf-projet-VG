<?php
declare(strict_types=1);

namespace App\Core;

final class Upload
{
    /**
     * Save uploaded files into /public/uploads/$subdir and return public URLs (starting with /uploads/...).
     * @return string[] public URLs
     */
    public static function saveImages(array $files, string $subdir = 'images'): array
    {
        $out = [];

        if (empty($files) || !isset($files['tmp_name'])) return $out;

        $root = dirname(__DIR__, 2); // /app -> project root
        $publicDir = $root . '/public';
        $targetDir = $publicDir . '/uploads/' . trim($subdir, '/');

        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0775, true);
        }

        $tmpNames = $files['tmp_name'];
        $names = $files['name'] ?? [];
        $errors = $files['error'] ?? [];
        $count = is_array($tmpNames) ? count($tmpNames) : 0;

        for ($i = 0; $i < $count; $i++) {
            if (!isset($errors[$i]) || $errors[$i] !== UPLOAD_ERR_OK) continue;
            $tmp = $tmpNames[$i];
            if (!is_uploaded_file($tmp)) continue;

            $orig = (string)($names[$i] ?? 'image');
            $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','webp','gif'])) $ext = 'jpg';

            $safeBase = preg_replace('/[^a-zA-Z0-9_-]+/', '_', pathinfo($orig, PATHINFO_FILENAME));
            $filename = $safeBase . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.' . $ext;

            $dest = $targetDir . '/' . $filename;
            if (@move_uploaded_file($tmp, $dest)) {
                $out[] = '/uploads/' . trim($subdir, '/') . '/' . $filename;
            }
        }

        return $out;
    }
}
