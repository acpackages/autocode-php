<?php

namespace AcExtensions;

trait AcBlobExtensions {
    public static function toBase64(string $filePath): string {
        if (!file_exists($filePath)) {
            throw new Exception("File not found: $filePath");
        }
        
        $fileData = file_get_contents($filePath);
        return base64_encode($fileData);
    }
}

?>
