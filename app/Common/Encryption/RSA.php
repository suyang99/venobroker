<?php declare(strict_types=1);
/**
 * RSA加密
 */
namespace App\Common\Encryption;

use Exception;

class RSA
{
    /**
     * 签名
     *
     * @param  string $content
     * @param  string $privateKey
     * @param  string $algorithm
     * @return string
     * @throws Exception
     */
    public static function signature(string $content, string $privateKey, string $algorithm): string
    {
        $signature  = '';
        $privateKey = "-----BEGIN PRIVATE KEY-----\n". $privateKey. "\n-----END PRIVATE KEY-----";
        $algorithm = match ($algorithm) {
            'SHA1'   => OPENSSL_ALGO_SHA1,
            'SHA256' => OPENSSL_ALGO_SHA256,
            'DSS1'   => OPENSSL_ALGO_DSS1,
            'MD2'    => OPENSSL_ALGO_MD2,
            'MD4'    => OPENSSL_ALGO_MD4,
            'MD5'    => OPENSSL_ALGO_MD5,
            'SHA224' => OPENSSL_ALGO_SHA224,
            'SHA384' => OPENSSL_ALGO_SHA384,
            'SHA512' => OPENSSL_ALGO_SHA512,
            'RMD160' => OPENSSL_ALGO_RMD160,
            default  => throw new Exception('The encryption mode is not supported')
        };
        openssl_sign($content, $signature, $privateKey, $algorithm);
        return base64_encode($signature);
    }

    /**
     * 验签
     *
     * @param  string $content
     * @param  string $signature
     * @param  string $publicKey
     * @param  string $algorithm
     * @return bool
     * @throws Exception
     */
    public static function verify(string $content, string $signature, string $publicKey, string $algorithm): bool
    {
        $publicKey = "-----BEGIN PUBLIC KEY-----\n". $publicKey. "\n-----END PUBLIC KEY-----";
        $algorithm = match ($algorithm) {
            'SHA1'   => OPENSSL_ALGO_SHA1,
            'SHA256' => OPENSSL_ALGO_SHA256,
            'DSS1'   => OPENSSL_ALGO_DSS1,
            'MD2'    => OPENSSL_ALGO_MD2,
            'MD4'    => OPENSSL_ALGO_MD4,
            'MD5'    => OPENSSL_ALGO_MD5,
            'SHA224' => OPENSSL_ALGO_SHA224,
            'SHA384' => OPENSSL_ALGO_SHA384,
            'SHA512' => OPENSSL_ALGO_SHA512,
            'RMD160' => OPENSSL_ALGO_RMD160,
            default  => throw new Exception('The encryption mode is not supported')
        };
        return (bool)openssl_verify($content, base64_decode($signature), $publicKey, $algorithm);
    }
}