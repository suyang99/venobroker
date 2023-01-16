<?php declare(strict_types=1);

namespace App\Service\Util;

use function nonce_str;

class PasswordService
{
    /**
     * 生成密码
     *
     * @param  string $password
     * @param  string $salt
     * @return string
     */
    public static function makePassword(string &$password, string &$salt): string
    {
        // 生成随机密码
        if (!$password) {
            $password = nonce_str(12);
        }
        // 生成加密随机数
        if (!$salt) {
            $salt = nonce_str(10, 'lun');
        }

        $res = md5(implode(':', [$password, $salt]));
        return strtoupper($res);
    }

    /**
     * 验证密码
     *
     * @param  string $password 新密码
     * @param  string $check    需验证的旧密码
     * @param  string $salt
     * @return bool
     */
    public static function checkPassword(string $password, string $check, string $salt): bool
    {
        $password = self::makePassword($password, $salt);
        return $password === $check;
    }
}