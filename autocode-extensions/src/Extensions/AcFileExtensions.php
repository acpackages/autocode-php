<?php

namespace AcExtensions;

trait AcFileExtensions {
    public static function toBlobObject(string $filePath): array {
        if (!file_exists($filePath)) {
            throw new Exception("File not found: $filePath");
        }
        
        return [
            'name' => basename($filePath),
            'lastModified' => filemtime($filePath),
            'size' => filesize($filePath),
            'type' => mime_content_type($filePath),
            'blob' => file_get_contents($filePath),
        ];
    }

    public static function toBytesObject(string $filePath): array {
        if (!file_exists($filePath)) {
            throw new Exception("File not found: $filePath");
        }
        
        return [
            'name' => basename($filePath),
            'lastModified' => filemtime($filePath),
            'size' => filesize($filePath),
            'type' => mime_content_type($filePath),
            'bytes' => array_values(unpack("C*", file_get_contents($filePath))),
        ];
    }
}

?>
