<?php declare(strict_types=1);

namespace App\Service\Market;

use App\Common\HttpClient;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

class HuobiService extends HttpClient
{
    protected array $options = [
        'base_uri' => 'https://api.huobi.pro/',
        'timeout'  => 2.0,
        'verify'   => false
    ];

    /**
     * 市场Kline
     *
     * @param  array $params
     * @return PromiseInterface|mixed|ResponseInterface|null
     */
    public function market(array $params): mixed
    {
        $res = $this->execute('market/history/kline', $params);
        if ($res) {
            $res = $res->getBody()->getContents();
            $res = json_decode($res);
        }
        return $res;
    }

    /**
     * 最近交易详情
     *
     * @param  string $symbol
     * @param  int    $size
     * @return mixed
     */
    public function lastTrade(string $symbol, int $size = 1): mixed
    {
        $params = [
            'symbol' => $symbol,
            'size'   => $size
        ];
        $res = $this->execute('market/history/trade', $params);
        if ($res) {
            $res = $res->getBody()->getContents();
            $res = json_decode($res);
        }
        return $res;
    }
}