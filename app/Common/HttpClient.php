<?php declare(strict_types=1);

namespace App\Common;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\RequestOptions;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Utils\ApplicationContext;
use Psr\Http\Message\ResponseInterface;
use Throwable;

abstract class HttpClient
{
    /**
     * @var ClientFactory
     */
    protected ClientFactory $clientFactory;

    /**
     * @var array
     */
    protected array $options = [
        'base_uri' => '',
        'timeout' => 2.0
    ];

    protected function __construct()
    {
        $this->clientFactory = ApplicationContext::getContainer()->get(ClientFactory::class);
    }

    public static function instance(): HttpClient
    {
        return new static();
    }

    public function getOption(string $key = '')
    {

    }

    public function setOption(string $key, $value)
    {

    }

    public function client(): Client
    {
        return $this->clientFactory->create($this->options);
    }

    public function get()
    {

    }

    public function post()
    {

    }

    /**
     * 执行
     *
     * @param  string $uri
     * @param  array  $params
     * @param  string $method
     * @param  bool   $async  异步
     * @return PromiseInterface|ResponseInterface|null
     */
    public function execute(string $uri, array $params = [], string $method = 'GET', bool $async = false): PromiseInterface|ResponseInterface|null
    {
        try {
            $method = $method ? explode(':', $method) : ['GET', ''];
            $type   = $method[1] ?? '';
            $method = $method[0];
            $client = $this->client();
            $params = $this->parseParams($params, $method, $type);
            return $async ?
                $client->requestAsync($method, $uri, $params) :
                $client->request($method, $uri, $params);
        } catch (Throwable $throwable) {
            Logger::error($throwable);
            return null;
        }
    }

    /**
     * 解析参数
     *
     * @param  array  $params
     * @param  string $method
     * @param  string $type
     * @return array
     */
    protected function parseParams(array $params, string $method, string $type = ''): array
    {
        if (! $params) return $params;

        $type = match (strtoupper($method)) {
            'POST' => match ($type) {
                'form_urlencode' => RequestOptions::FORM_PARAMS,
                'form', 'file'   => RequestOptions::MULTIPART,
                default          => RequestOptions::JSON
            },
            default => RequestOptions::QUERY,
        };
        return [$type => $params];
    }
}