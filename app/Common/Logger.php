<?php declare(strict_types=1);

namespace App\Common;

use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Utils\Stringable;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class Logger
{
    public function __invoke(ContainerInterface $container): LoggerInterface
    {
        return self::instance();
    }

    /**
     * @param  string $name
     * @param  string $config
     * @return LoggerInterface
     */
    public static function instance(string $name = 'app', string $config = 'default'): LoggerInterface
    {
        return ApplicationContext::getContainer()->get(LoggerFactory::class)->get($name, $config);
    }

    /**
     * 信息
     *
     * @param  string $name
     * @param  mixed  $message
     * @return void
     */
    public static function info(string $name, mixed $message)
    {
        $instance = self::instance();
        if (is_array($message) || $message instanceof Arrayable) {
            $message = json_encode($message);
        }
        $message = "[$name] ". $message;
        $instance->info($message);
    }

    /**
     * 错误
     *
     * @param  mixed $error
     * @return void
     */
    public static function error(mixed $error): void
    {
        $instance = self::instance();
        if ($error instanceof Throwable) {
            $instance->error($error->getMessage(), [$error->getFile(), $error->getLine()]);
        } elseif (is_array($error)) {
            $msg = array_pop($error);
            $instance->error($msg, $error);
        } else {
            $instance->error($error);
        }
    }

    /**
     * @param  string $name
     * @param  array $arguments
     * @return LoggerInterface
     */
    public static function __callStatic(string $name, array $arguments)
    {
        if ('name' === $name) {
            $arguments = $arguments ? array_pop($arguments) : '';
            return self::instance(name: $arguments);
        } elseif ('config' === $name) {
            $arguments = $arguments ? array_pop($arguments) : '';
            return self::instance(config: $arguments);
        } else {
            return self::instance()->$name(...$arguments);
        }
    }
}