<?php declare(strict_types=1);

namespace App\Controller\Api\Trade;

use App\Controller\AbstractController;
use App\Logic\Trade\GoodsLogic;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Psr\Http\Message\ResponseInterface;

#[Controller(prefix: '/trade', server: 'apiHttp')]
class GoodsController extends AbstractController
{
    /**
     * @var GoodsLogic|null
     */
    #[Inject]
    protected ?GoodsLogic $goodsLogic = null;

    /**
     * 产品列表
     *
     * @return ResponseInterface
     */
    #[GetMapping(path: 'goods')]
    public function goods(): ResponseInterface
    {
        $data = $this->goodsLogic->huobi(['limit'=> 100]);
        return $this->success($data);
    }

    /**
     * 产品详情
     *
     * @return ResponseInterface
     */
    #[GetMapping(path: 'goods/detail')]
    public function detail(): ResponseInterface
    {
        $id  = $this->request->query('id', 0);
        $res = $this->goodsLogic->detail((int)$id);
        return $this->result(...$res);
    }
}