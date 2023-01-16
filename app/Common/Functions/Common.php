<?php declare(strict_types=1);

use App\Common\Redis;
use App\Logic\Common\AuthenticationLogic;
use App\Service\Util\ExchangeRateService;

if (! function_exists('nonce_str')) {
    /**
     * 生成随机字符串.
     *
     * @param int $length 串长度
     * @param string $type 包含的类型 l小写字母 u大写字母 n数组 s符号
     * @param string $sign 自定义符号 不输入则使用默认
     */
    function nonce_str(int $length, string $type = '', string $sign = ''): string
    {
        $num   = '0123456789876543210';
        $lower = 'abcdefghlkmnopqrstuvwxyz';
        $upper = 'ABCDEFGHLKMNPQRSTUVWXYZ';
        $sign  = ! $sign ? '!@#$%*()-_+=,.' : $sign;
        $src_str = '';

        if (! $type) {
            $src_str = $num . $lower . $upper . $sign;
        } else {
            ! str_contains($type, 'n') ?: $src_str .= $num;
            ! str_contains($type, 'l') ?: $src_str .= $lower;
            ! str_contains($type, 'u') ?: $src_str .= $upper;
            ! str_contains($type, 's') ?: $src_str .= $sign;
        }

        return $src_str ? substr(str_shuffle($src_str), 0, $length) : '';
    }
}

if (! function_exists('auth')) {
    /**
     * 授权逻辑
     *
     * @return AuthenticationLogic|mixed
     */
    function auth(): mixed
    {
        return make(AuthenticationLogic::class);
    }
}

if (! function_exists('parseMessage')) {
    /**
     * 解析消息
     *
     * @param  string|array $message
     * @return string
     */
    function parseMessage(string|array $message): string
    {
        if (! $message) {
            $message = '';
        } elseif (is_array($message)) {
            $message = __(...$message);
        } else {
            $message = __($message);
        }
        if (is_array($message)) {
            $message = __('message.error.unknown');
        }
        return $message;
    }
}

if (! function_exists('getExchangeRate')) {
    function getExchangeRate (string $from , string $to, int $amount = 1, string $date = '')
    {
        $rate = Redis::get('system:rate');
        if (!$rate) {
            $rate = ExchangeRateService::instance()->convert($from, $to, $amount, $date);
            if ($rate) {
                $rate = $rate->info->rate;
//                $rate = round($amount/$rate, 6);
                Redis::setEx('system:rate', 24*3600, $rate);
            }
        }
        return $rate;
    }
}