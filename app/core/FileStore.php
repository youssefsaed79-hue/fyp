<?php
declare(strict_types=1);

namespace App\Core;

/**
 * File Storage and Secure Download Handler (NFR-PRIV-01, NFR-PERF-02)
 * Uploads are stored privately outside the web root and served ONLY through
 * authenticated download actions with MIME and extension validation.
 */
class FileStore
{
    private static ?array $config = null;

    public static function init(array $config): void
    {
        self::$config = $config;
    }

    private static function getConfig(): array
    {
        if (self::$config === null) {
            $fullConfig = require dirname(__DIR__) . '/config/config.php';
            self::$config = $fullConfig['uploads'];
        }
        return self::$config;
    }

    /**
     * Handle single file upload with validation
     * Returns array with metadata or throws Exception
     */
    public static function store(array $fileData, string $subfolder = 'documents'): array
    {
        $cfg = self::getConfig();

        if (!isset($fileData['error']) || is_array($fileData['error'])) {
            throw new \RuntimeException('Invalid file upload parameters.');
        }

        switch ($fileData['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new \RuntimeException('No file was uploaded.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new \RuntimeException('Uploaded file exceeds server size limits.');
            default:
                throw new \RuntimeException('Unknown error during file upload.');
        }

        // Validate file size (10 MB max)
        $maxBytes = $cfg['max_size_bytes'] ?? (10 * 1024 * 1024);
        if ($fileData['size'] > $maxBytes) {
            throw new \RuntimeException("File size exceeds the 10 MB limit.");
        }

        // Validate extension
        $originalName = basename($fileData['name']);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        $allowedTypes = $cfg['allowed_types'] ?? [];
        if (!array_key_exists($ext, $allowedTypes)) {
            throw new \RuntimeException("Invalid file type (extension .{$ext} not permitted). Only PDF, DOCX, PPTX and images are allowed.");
        }

        // Validate MIME type with finfo
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($fileData['tmp_name']);
        $expectedMimes = $allowedTypes[$ext];

        if (!in_array($realMime, $expectedMimes, true)) {
            // Some zip formats for docx/pptx may be generic application/zip or application/octet-stream
            $isOfficeDoc = in_array($ext, ['docx', 'pptx', 'doc', 'ppt'], true) &&
                in_array($realMime, ['application/zip', 'application/octet-stream', 'application/x-zip-compressed'], true);

            if (!$isOfficeDoc) {
                throw new \RuntimeException("File contents do not match MIME type (detected: {$realMime}).");
            }
        }

        // Generate private storage path
        $storageDir = rtrim($cfg['storage_path'], '/\\') . '/' . trim($subfolder, '/\\');
        if (!is_dir($storageDir)) {
            @mkdir($storageDir, 0755, true);
        }

        $storedName = bin2hex(random_bytes(16)) . '.' . $ext;
        $targetPath = $storageDir . '/' . $storedName;

        if (!move_uploaded_file($fileData['tmp_name'], $targetPath)) {
            throw new \RuntimeException("Failed to move uploaded file to private storage.");
        }

        return [
            'original_name' => $originalName,
            'stored_name' => $subfolder . '/' . $storedName,
            'mime' => $realMime,
            'size' => (int) $fileData['size'],
            'ext' => $ext,
        ];
    }

    /**
     * Download or view private file
     */
    public static function serve(string $storedPath, string $downloadName, string $mime = '', bool $asAttachment = false): void
    {
        $cfg = self::getConfig();
        $fullPath = rtrim($cfg['storage_path'], '/\\') . '/' . ltrim($storedPath, '/\\');

        if (!file_exists($fullPath)) {
            http_response_code(404);
            die("Requested document does not exist.");
        }

        if (empty($mime)) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($fullPath) ?: 'application/octet-stream';
        }

        // Prevent header injection and buffer flushing
        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($fullPath));
        $disposition = $asAttachment ? 'attachment' : 'inline';
        header('Content-Disposition: ' . $disposition . '; filename="' . rawurlencode($downloadName) . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        readfile($fullPath);
        exit;
    }

    /**
     * Delete stored file
     */
    public static function delete(string $storedPath): bool
    {
        $cfg = self::getConfig();
        $fullPath = rtrim($cfg['storage_path'], '/\\') . '/' . ltrim($storedPath, '/\\');
        if (file_exists($fullPath)) {
            return @unlink($fullPath);
        }
        return false;
    }
}
