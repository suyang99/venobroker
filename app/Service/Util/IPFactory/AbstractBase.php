<?php declare(strict_types=1);

namespace App\Service\Util\IPFactory;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Guzzle\ClientFactory;

abstract class AbstractBase
{
    /**
     * @var ClientFactory|null
     */
    #[Inject]
    private ?ClientFactory $factory = null;

    /**
     * 解析IP地址
     *
     * @param  array $ip
     * @return array
     */
    abstract public function parseIPAddress(array $ip): array;

    /**
     * 解析查询结果
     *
     * @param  string $response
     * @return array
     */
    abstract protected function parseResult(string $response): array;

    /**
     * 执行查询请求
     *
     * @param  string $url
     * @param  array  $params
     * @param  string $method
     * @return void
     */
    protected function doRequest(string $url, array $params, string $method = 'GET')
    {
        $client = $this->factory->create();
    }
}