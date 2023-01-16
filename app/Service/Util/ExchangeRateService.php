<?php declare(strict_types=1);

namespace App\Service\Util;

use App\Common\HttpClient;
use App\Common\Logger;

class ExchangeRateService extends HttpClient
{
    protected array $options = [
        'base_uri' => 'https://api.apilayer.com/exchangerates_data/',
        'timeout'  => 2.0,
        'verify'   => false,
        'headers'  => [
            'apikey' => 'ELifXV886xUk58QtoFGzAC6eS9LhuHVB'
        ]
    ];

    /**
     * 汇率
     *
     * @param  string $from
     * @param  string $to
     * @param  int    $amount
     * @param  string $date
     * @return mixed
     */
    public function convert(string $from , string $to, int $amount = 1, string $date = ''): mixed
    {
        $params = [
            'to' => $to,
            'from' => $from,
            'amount' => $amount,
        ];
        if ($date) {
            $params['date'] = $date;
        }
        $res = $this->execute('convert', $params);
        if ($res) {
            $res = $res->getBody()->getContents();
//            $res = json_decode($res);
        }
        return json_decode($res);
    }
}