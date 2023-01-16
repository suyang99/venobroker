<?php declare(strict_types=1);

namespace App\Traits;

use App\Exception\ApiException;

trait ArrayResultTraits
{
    /**
     * 返回结果
     *
     * @param  int          $code
     * @param  string|array $message
     * @param  mixed        $data
     * @return array
     */
    protected function result(int $code, string|array $message, mixed $data): array
    {
        return ['code'=> $code, 'message'=> $message, 'data'=> $data];
    }

    /**
     * 错误
     *
     * @param  string|array $message
     * @param  int          $code
     * @return array
     */
    protected function error(string|array $message = 'FAIL', int $code = 10000): array
    {
        return $this->result($code, $message, null);
    }

    /**
     * 成功
     *
     * @param  mixed        $data
     * @param  string|array $message
     * @return array
     */
    protected function success(mixed $data = null, string|array $message = 'SUCCESS'): array
    {
        return $this->result(0, $message, $data);
    }

    /**
     * @param  string|array $message
     * @param  int          $code
     * @return mixed
     */
    protected function exception(string|array $message = '', int $code = 10000): mixed
    {
        $message = $message ?: 'message.error';
        $message = parseMessage($message);
        throw new ApiException($message, $code);
    }
}