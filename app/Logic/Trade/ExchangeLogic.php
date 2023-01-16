<?php declare(strict_types=1);

namespace App\Logic\Trade;

use App\Common\Logger;
use App\Common\Redis;
use App\Logic\AbstractLogic;
use App\Logic\Account\RecodeLogic;
use App\Model\GoodsModel;
use App\Model\OrderExchange;
use App\Model\UserAccountModel;
use App\Service\Market\HuobiService;
use Hyperf\DbConnection\Db;
use Throwable;

class ExchangeLogic extends AbstractLogic
{
    public function purchase(int $userId, array $params)
    {
        $direction = match ($params['direction']) {
            'call'  => 1,
            'put'   => 2,
            default => $this->exception('message.error.params', 10600)
        };
        $amount    = (int)ceil($params['amount'] * 100);

        Db::beginTransaction();
        try {
            // 用户账户
            $account = UserAccountModel::query()->where('id', $userId)->lockForUpdate()->first();
            if (! $account) {
                Db::rollBack();
                return $this->error('user.error.not_exist');
            }
            // 余额不足
            if ($account->balance < $amount) {
                return $this->error('user.account.balance_ins', 10601);
            }
            // 验证最低最高购买金额
            $tradeLimit = $this->getConfig('trade_low');
            $tradeLimit = explode(',',  $tradeLimit);
            foreach ($tradeLimit as $key => $value) {
                $value = strpos($value, '%') ? intval($value) * $account->balance / 100 : (int)$value * 100;
                if ($key) { // 最高
                    if ($amount > $value) {
                        return $this->error(['trade.purchase.maximum', ['field'=>$amount]], 10603);
                    }
                } else {  // 最低
                    if ($amount < $value) {
                        return $this->error(['trade.purchase.minimum', ['field'=>$amount]], 10602);
                    }
                }
            }
            // 挂单时长
            $isPairs = Redis::get('trade:'. $userId);
            if ($isPairs) {
                return $this->error('trade.purchase.pairs', 10604);
            }
            // 查询产品
            $goods = $params['goods_id'];
            $goods = GoodsModel::where('id', $goods)->where('status', 1)->first();
            if (! $goods) {
                return $this->error(['message.error.not_exist', ['field'=> 'Goods']], 10605);
            }
            // 创建订单
            $exchange = new OrderExchange();
            $data     = [
                'user_id'   => $userId,
                'goods_id'  => $params['goods_id'],
                'order_num' => $this->makeOrderId(),
                'amount'    => $amount,
                'direction' => $direction,
                'status'    => 1,
            ];
            foreach ($data as $key=> $value) {
                $exchange->$key = $value;
            }
            if (! $exchange->save()) {
                Db::rollBack();
                return $this->error('trade.purchase.fail', 10606);
            }
            // 扣除账户金额
            $account->balance = Db::raw('balance - '. $amount);
            if (! $account->save()) {
                Db::rollBack();
                return $this->error('trade.purchase.fail', 10607);
            }
            // 记录日志
            make(RecodeLogic::class)->record(
                $amount,
                $userId,
                3
            );
            Db::commit();
            return $this->success();
        } catch (Throwable $throwable) {
            Db::rollBack();
            Logger::error($throwable);
            $this->exception('trade.purchase.fail', 10610);
        }
    }

    /**
     * 完成订单
     *
     * @param  int $id
     * @param  int $direction
     * @return array
     */
    public function completeOrder(int $id, int $direction): array
    {
        Db::beginTransaction();
        try {

        } catch (Throwable $throwable) {
            Db::rollBack();
            Logger::error($throwable);
            $this->exception(code: 10620);
        }
    }

    /**
     * 交易记录
     *
     * @param  int   $userId
     * @param  array $params
     * @return array
     */
    public function record(int $userId, array $params): array
    {
        $type   = ($params['type'] ?? '') == 'history' ? 2 : 1;
        $page   = (int)($params['page']  ?? 1);
        $size   = (int)($params['size']  ?? 10);
        $record = OrderExchange::where('user_id', $userId)
            ->where('status', $type)
            ->paginate(
                perPage: $size,
                page:    $page
            );
        if ($record->isNotEmpty()) {
            $data  = $record->items();
            // 产品名称
            $goods = array_column($data, 'goods_id');
            $goods = GoodsModel::whereIn('id', array_unique($goods))->pluck('name', 'id');
            foreach ($data as &$item) {
                $this->formatAmount($item, 'amount,gain');
                $item['goods_name'] = $goods[$item->goods_id];
            }
        }

        $record = [
            'list'  => $record->items(),
            'total' => $record->total()
        ];
        return $this->success($record);
    }
}