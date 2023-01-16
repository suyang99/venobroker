<?php declare(strict_types=1);

namespace App\Controller\Api\User;

use App\Controller\AbstractController;
use App\Logic\Account\AccountLogic;
use App\Logic\Account\RecodeLogic;
use App\Validation\AccountValidator;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Psr\Http\Message\ResponseInterface;

#[Controller(prefix: '/user/account', server: 'apiHttp')]
class AccountController extends AbstractController
{
    #[Inject]
    protected ?AccountLogic $accountLogic = null;

    #[Inject]
    protected ?RecodeLogic $recodeLogic = null;

    #[Inject]
    protected ?AccountValidator $accountValidator = null;

    /**
     * 账户信息
     *
     * @return ResponseInterface
     */
    #[GetMapping(path: 'info')]
    public function info(): ResponseInterface
    {
        $res = $this->accountLogic->info(auth()->userId(), ['bank']);
        return $this->result(...$res);
    }

    /**
     * 账户余额
     *
     * @return ResponseInterface
     */
    #[GetMapping(path: 'balance')]
    public function balance(): ResponseInterface
    {
        ['data'=> $res] = $this->accountLogic->info(auth()->userId());
        $res = ['balance' => $res['balance']];
        return $this->success($res);
    }

    /**
     * 绑定银行卡
     *
     * @return ResponseInterface
     */
    #[PostMapping(path: 'bind_bank')]
    public function bindBank(): ResponseInterface
    {
        $params = $this->request->post();
        $params = $this->accountValidator->bindBank($params);
        $res    = $this->accountLogic->bindBank(auth()->userId(), $params);
        return $this->result(...$res);
    }

    /**
     * 充值申请
     *
     * @return ResponseInterface
     */
    #[PostMapping(path: 'recharge')]
    public function recharge(): ResponseInterface
    {
        $amount = $this->request->post();
        $amount = $this->accountValidator->apply($amount);
        $amount = ceil((float)($amount['amount']) * 100);
        $res    = $this->accountLogic->recharge(auth()->userId(), (int)$amount);
        return $this->result(...$res);
    }

    /**
     * 提现申请
     *
     * @return ResponseInterface
     */
    #[PostMapping(path: 'withdraw')]
    public function withdraw(): ResponseInterface
    {
        $amount = $this->request->post();
        $amount = $this->accountValidator->apply($amount);
        $amount = ceil((float)($amount['amount']) * 100);
        $res    = $this->accountLogic->withdraw(auth()->userId(), (int)$amount);
        return $this->result(...$res);
    }

    /**
     * 账户资金记录
     *
     * @return ResponseInterface
     */
    #[GetMapping(path: 'record')]
    public function record(): ResponseInterface
    {
        $res  = $this->recodeLogic->getRecord(auth()->userId(), $this->request->query());
        return $this->result(...$res);
    }
}