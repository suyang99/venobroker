<?php declare(strict_types=1);

namespace App\Exception\Handler;

use App\Constants\ErrorCode;
use Hyperf\HttpMessage\Exception\NotFoundHttpException;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ErrorExceptionHandler extends BaseExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        if ($throwable instanceof NotFoundHttpException) {
            // 阻止异常冒泡
            $this->stopPropagation();
            return $response->withStatus(404)->withBody(new SwooleStream($throwable->getMessage()));
        }

        $this->saveError($throwable);

        $data = json_encode([
            'code'    => ErrorCode::SERVER_ERROR,
            'message' => __('message.error.server'),
            'data'    => null
        ], JSON_UNESCAPED_UNICODE);

        // 阻止异常冒泡
        $this->stopPropagation();
        return $response->withBody(new SwooleStream($data));
    }
}