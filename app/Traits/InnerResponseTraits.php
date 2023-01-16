<?php declare(strict_types=1);

namespace App\Traits;

trait InnerResponseTraits
{
    /**
     * 返回结果
     *
     * @param  int    $code
     * @param  string $message
     * @param  mixed  $data
     * @return array
     */
    protected function result(int $code, string $message, mixed $data): array
    {
        return ['code'=> $code, 'message'=> $message, 'data'=> $data];
    }

    /**
     * 错误
     *
     * @param  string $message
     * @param  int    $code
     * @return array
     */
    protected function error(string $message = 'FAIL', int $code = 10000): array
    {
        return $this->result($code, $message, null);
    }

    /**
     * 成功
     *
     * @param  mixed  $data
     * @param  string $message
     * @return array
     */
    protected function success(mixed $data = '', string $message = 'SUCCESS'): array
    {
        return $this->result(0, $message, $data);
    }
}