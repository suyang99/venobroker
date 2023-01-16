<?php declare(strict_types=1);

namespace App\Logic\Trade;

use App\Common\Logger;
use App\Logic\AbstractLogic;
use App\Model\GoodsModel;
use App\Service\Market\HuobiService;
use Throwable;

class GoodsLogic extends AbstractLogic
{
    /**
     * 产品详情
     *
     * @param  int $id
     * @return array|void
     */
    public function detail(int $id)
    {
        try {
            $goods = GoodsModel::where('id', $id)->first();
            if (! $goods) {
                return $this->error('trade.goods.not_exist', 10401);
            }
            // 远程数据
            $service = HuobiService::instance();
            $res     = $service->market([
                'symbol' => $goods['symbol'],
                'period' => $goods['period'],
                'size'   => $goods['size']
            ]);
            return $this->success($res->data);
        } catch (Throwable $throwable) {
            Logger::error($throwable);
            $this->exception(code: 10400);
        }
    }

    /**
     * 产品列表
     *
     * @param  array $pages
     * @return array
     */
    public function list(array $pages = []): array
    {
        $limit = (int)($pages['limit'] ?? 0);
        $page  = (int)($pages['page']  ?? 1);
        $size  = (int)($pages['size']  ?? 10);
        $goods = GoodsModel::where('status', 1)->orderByDesc('id');
        if ($limit) {
            $data = $goods->limit($limit)->get()->toArray();
        } else {
            $data = $goods->paginate(
                perPage: $size,
                page:    $page
            );
            $data = [
                'list'  => $data->items(),
                'total' => $data->total()
            ];
        }
        return $data;
    }

    public function huobi(array $page): array
    {
        $goods   = $this->list($page);
        $service = HuobiService::instance();
        foreach ($goods as &$value) {
            $data  = $service->market([
                'symbol' => $value['symbol'],
                'period' => $value['period'],
                'size'   => 1
            ]);
            $value = [
                'id'        => $value['id'],
                'name'      => $value['name'],
                'data'      => $data->data[0],
                'direction' => $value['direction']
            ];
        }
        $goods = [
            'list' => $goods,
            'rate' => getExchangeRate('USD', 'INR'),
        ];
        return $goods;
    }
}