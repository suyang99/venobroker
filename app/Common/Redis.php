<?php declare(strict_types=1);

namespace App\Common;

use App\Exception\ApiException;
use Exception;
use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;
use Hyperf\Utils\ApplicationContext;
use Throwable;

/**
 * @method static get($key)
 * @method static set($key, $value, $timeout = null)
 * @method static setEx($key, $ttl, $value)
 * @method static pSetEx($key, $ttl, $value)
 * @method static setNx($key, $value)
 * @method static del($key1, ...$otherKeys)
 * @method static hGetAll($key)
 * @method static hMSet($key, $hashKeys)
 */
class Redis
{
    /**
     * @throws Exception
     */
    public static function __callStatic(string $name, array $arguments)
    {
        return self::instance()->{$name}(...$arguments);
    }

    /**
     * 实例
     *
     * @throws ApiException
     */
    public static function instance(string $poolName = ''): RedisProxy
    {
        self::parsePoolName($poolName);
        try {
            $container = ApplicationContext::getContainer()->get(RedisFactory::class);
            return $container->get($poolName);
        } catch (Throwable $throwable) {
            throw new ApiException('Description Failed to read the Redis configuration.', 10000);
        }
    }

    /**
     * 解析配置
     *
     * @param  string $poolName
     * @return void
     */
    protected static function parsePoolName(string &$poolName)
    {
        if (!$poolName || !in_array($poolName, config('redis'))) {
            $poolName = 'default';
        }
    }
}
