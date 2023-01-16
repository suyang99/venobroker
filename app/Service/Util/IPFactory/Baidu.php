<?php declare(strict_types=1);

namespace App\Service\Util\IPFactory;

use function Swoole\Coroutine\Http\request;

class Baidu extends AbstractBase
{
    protected string $host = 'opendata.baidu.com/api.php';

    protected array $param = [
        'query'       => '',
        'co'          => '',
        'resource_id' => 6006,
        'oe'          => 'utf8'
    ];

    public function parseIPAddress(array $ip): array
    {
        // TODO: Implement parseIPAddress() method.
        return [];
    }

    protected function parseResult(string $response): array
    {
        // TODO: Implement parseResult() method.
        return [];
    }
}