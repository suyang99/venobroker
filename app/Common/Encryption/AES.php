<?php declare(strict_types=1);
/**
 *
 */
namespace App\Common\Encryption;

class AES
{
    public const CIPHER_ALO_AES256CBC = 'AES-256-CBC';

    public static function encrypt(string $content, string $key, string $cipher = self::CIPHER_ALO_AES256CBC): string
    {
        $content = openssl_encrypt($content, $cipher, $key, OPENSSL_RAW_DATA);
        return base64_encode($content);
    }

    public static function decrypt(string $content, string $key, string $cipher = self::CIPHER_ALO_AES256CBC): bool|string
    {
        return openssl_decrypt(base64_decode($content), $cipher, $key, OPENSSL_RAW_DATA);
    }
}