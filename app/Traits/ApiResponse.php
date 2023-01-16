<?php declare(strict_types=1);

namespace App\Traits;

use Hyperf\Context\Context;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Utils\Contracts\Jsonable;
use Psr\Http\Message\ResponseInterface;

/**
 * Trait ApiResponse
 * @package App\Traits
 */
trait ApiResponse
{
    private int    $httpCode  = 200;
    private int    $errorCode = 100000;
    private string $errorMsg  = '系统错误';
    private array  $headers   = [];

    protected ResponseInterface $response;

    /**
     * 对内部输出
     *
     * @param  int          $code
     * @param  string|array $message
     * @param  mixed        $data
     * @return ResponseInterface
     */
    public function result(int $code = 0, string|array $message = '', mixed $data = null): ResponseInterface
    {
        if (! $code) {
            return $this->success($data, $message);
        } else {
            return $this->error($message, $code, $data);
        }
    }

    /**
     * 成功响应
     *
     * @param  mixed        $data
     * @param  string|array $message
     * @return ResponseInterface
     */
    public function success(mixed $data = '', string|array $message = ''): ResponseInterface
    {
        return $this->respond([
            'code'    => 0,
            'message' => $message ? parseMessage($message) : 'SUCCESS',
            'data'    => $data
        ]);
    }

    /**
     * 错误返回
     *
     * @param  string|array $message 错误信息
     * @param  int|null     $code    错误业务码
     * @param  mixed        $data    额外返回的数据
     * @return ResponseInterface
     */
    public function error(string|array $message, int $code = null, mixed $data = null): ResponseInterface
    {
        return $this->respond([
                'code'    => $code    ?? $this->errorCode,
                'message' => $message ? parseMessage($message) : 'FAIL',
                'data'    => $data
            ]);
    }

    /**
     * 设置http返回码
     *
     * @param  int $code    http返回码
     * @return $this
     */
    final public function setHttpCode(int $code = 200): self
    {
        $this->httpCode = $code;
        return $this;
    }

    /**
     * 设置返回头部header值
     *
     * @param  string $key
     * @param  mixed  $value
     * @return $this
     */
    public function addHttpHeader(string $key, mixed $value): self
    {
        $this->headers += [$key => $value];
        return $this;
    }

    /**
     * 批量设置头部返回
     *
     * @param  array $headers    header数组：[key1 => value1, key2 => value2]
     * @return $this
     */
    public function addHttpHeaders(array $headers = []): self
    {
        $this->headers += $headers;
        return $this;
    }

    /**
     * @param  mixed $response
     * @return ResponseInterface
     */
    private function respond(mixed $response): ResponseInterface
    {
        if (is_string($response)) {
            return $this->response()->withAddedHeader('content-type', 'text/plain')->withBody(new SwooleStream($response));
        }

        if (is_array($response) || $response instanceof Arrayable) {
            return $this->response()
                ->withAddedHeader('content-type', 'application/json')
                ->withBody(new SwooleStream(Json::encode($response)));
        }

        if ($response instanceof Jsonable) {
            return $this->response()
                ->withAddedHeader('content-type', 'application/json')
                ->withBody(new SwooleStream((string)$response));
        }

        return $this->response()->withAddedHeader('content-type', 'text/plain')->withBody(new SwooleStream((string)$response));
    }

    /**
     * 获取 Response 对象
     *
     * @return ResponseInterface
     */
    protected function response(): ResponseInterface
    {
        $response = Context::get(ResponseInterface::class);
        foreach ($this->headers as $key => $value) {
            $response = $response->withHeader($key, $value);
        }
        return $response;
    }

    /**
     *
     * @param  string|array $message
     * @return string
     */
    protected function parseMessage(string|array $message): string
    {
        if (! $message) {
            $message = '';
        } elseif (is_array($message)) {
            $message = __(...$message);
        } else {
            $message = __($message);
        }
        if (is_array($message)) {
            $message = __('message.unknown');
        }
        return $message;
    }
}