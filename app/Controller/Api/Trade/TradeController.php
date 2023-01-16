<?php declare(strict_types=1);

namespace App\Controller\Api\Trade;

use App\Controller\AbstractController;
use App\Logic\Trade\ExchangeLogic;
use App\Validation\TradeValidator;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Psr\Http\Message\ResponseInterface;

#[Controller(prefix: '/trade', server: 'apiHttp')]
class TradeController extends AbstractController
{
    #[Inject]
    protected ?TradeValidator $tradeValidator = null;

    #[Inject]
    protected ?ExchangeLogic $exchangeLogic = null;

    #[PostMapping(path: 'purchase')]
    public function purchase(): ResponseInterface
    {
        $params = $this->request->post();
        $params = $this->tradeValidator->purchase($params);
        $res    = $this->exchangeLogic->purchase(auth()->userId(), $params);
        return $this->result(...$res);
    }

    /**
     * 交易记录
     *
     * @return ResponseInterface
     */
    #[GetMapping(path: 'record')]
    public function record(): ResponseInterface
    {
        $params = $this->request->query();
        $res    = $this->exchangeLogic->record(auth()->userId(), $params);
        return $this->success($res);
    }
}