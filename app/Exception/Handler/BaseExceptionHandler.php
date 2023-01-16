<?php declare(strict_types=1);

namespace App\Exception\Handler;

use App\Common\Logger;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Throwable;

abstract class BaseExceptionHandler extends ExceptionHandler
{
    /**
     * @param  Throwable $throwable
     * @return bool
     */
    public function isValid(Throwable $throwable): bool
    {
        return true;
    }

    /**
     * 记录错误
     *
     * @param  Throwable $throwable
     * @return void
     */
    protected function saveError(Throwable $throwable)
    {
        $logger  = Logger::instance();
        $logger->error($throwable->getMessage(), [$throwable->getFile(),$throwable->getLine()], '');
    }
}