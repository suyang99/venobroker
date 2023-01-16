<?php declare(strict_types=1);

namespace App\Exception\Handler;

use App\Exception\ApiException;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ApiExceptionHandler extends BaseExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        if ($throwable instanceof ApiException) {
            $data = json_encode([
                'code'    => $throwable->getCode(),
                'message' => $throwable->getMessage(),
                'data'    => null
            ], JSON_UNESCAPED_UNICODE);

            // 阻止异常冒泡
            $this->stopPropagation();
            return $response->withBody(new SwooleStream($data));
        }

        return $response;
    }
}
