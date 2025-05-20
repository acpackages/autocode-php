<?php

namespace Autocode;

class AcEncryption {
    public static string $encryptionKey = "###RandomEncryptionKey###";
    public static string $iv = hex2bin('000102030405060708090a0b0c0d0e0f');
    static function decrypt(object $encryptedText,?string $encryptionKey = null): string {
        $key = self::$encryptionKey;
        if($encryptionKey!=null){
            $key = $encryptionKey;
        }
        $hashedKey = hash('sha256', $key, true);
        $iv = hex2bin(self::$iv);    
        $ciphertext = base64_decode($encryptedText);
        return openssl_decrypt($ciphertext, 'AES-256-CBC', $hashedKey, OPENSSL_RAW_DATA, $iv);
    }

    static function encrypt(string $plainText,?string $encryptionKey = null): string {
        $key = self::$encryptionKey;
        if($encryptionKey!=null){
            $key = $encryptionKey;
        }
        $hashedKey = hash('sha256', $key, true);
        $iv = hex2bin(self::$iv);
        $encrypted = openssl_encrypt($plainText, 'AES-256-CBC', $hashedKey, OPENSSL_RAW_DATA, $iv);
        return base64_encode($encrypted);
    }
}
?>