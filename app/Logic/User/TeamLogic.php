<?php declare(strict_types=1);

namespace App\Logic\User;

use App\Logic\AbstractLogic;
use App\Model\OrderExchange;
use App\Model\UserAccountLogModel;
use App\Model\UserModel;
use App\Model\UserRechargeModel;
use App\Model\UserWithdrawModel;

class TeamLogic extends AbstractLogic
{
    public function team(int $userId)
    {
        $total = [
            'children'  => 0,   // 成员数量
            'recharged' => 0,   // 充值金额
            'consume'   => 0,   // 消费金额
        ];
        $levelTmp = [
            'commissio' => 0,   // 返佣金额
            'members'   => 0,   // 成员数量
            'orders'    => 0,   // 下单数量
            'deposit'   => 0,   // 下单金额
            'withdraw'  => 0,   // 提现金额
        ];
        $withdraw = [];
        $consume  = [];
        $rebate   = [];
        $level    = [];

        $model    = new UserModel();
        $children = $model->family($userId, 3, ['id']);
        $members  = array_keys($children);
        // 充值统计
        $data = UserRechargeModel::whereIn('user_id', $members)
            ->where('status', 2)
            ->selectRaw('user_id, SUM(amount) as amount')
            ->groupBy(['user_id'])
            ->get()
            ->toArray();
        foreach ($data as $item) {
            $total['recharged'] += $item['amount'];
        }
        $total['children'] = count($members);
        // 提现统计
        $data = UserWithdrawModel::whereIn('user_id', $members)
            ->where('status', 2)
            ->selectRaw('COUNT(id) as num, user_id, SUM(amount) as amount')
            ->groupBy(['user_id'])
            ->get()
            ->toArray();
        foreach ($data as $item) {
            $withdraw[$item['user_id']] = $item;
        }
        // 消费统计
        $data = OrderExchange::whereIn('user_id', $members)
            ->where('status', 2)
            ->selectRaw('COUNT(id) as num, user_id, SUM(amount) as amount')
            ->groupBy(['user_id'])
            ->get()
            ->toArray();
        foreach ($data as $item) {
            $consume[$item['user_id']] = $item;
        }
        // 返佣统计
        $data = UserAccountLogModel::whereIn('child_id', $members)
            ->whereIn('type', [4, 5, 6])
            ->selectRaw('user_id, SUM(amount) as amount')
            ->groupBy(['user_id'])
            ->get()
            ->toArray();
        foreach ($data as $item) {
            $rebate[$item['user_id']] = $item;
        }

        // 最终统计
        foreach ($children as $key=> $value) {
            $current = 'level'. $value['level'];
            if (! isset($level[$current])) {
                $level[$current] = $levelTmp;
            }
            // 分级
            $level[$current]['commissio'] += $rebate[$key]['amount'] ?? 0;
            $level[$current]['members']   += 1;
            $level[$current]['orders']    += $consume[$key] ?? 0;
            $level[$current]['deposit']   += $consume[$key]['amount'] ?? 0;
            $level[$current]['withdraw']  += $withdraw[$key]['amount'] ?? 0;

            $total['consume'] += $consume[$key]['amount'] ?? 0;
        }
        for ($i = 1; $i < 4; ++$i) {
            $key = 'level'. $i;
            if (! isset($level[$key])) {
                $level[$key] = $levelTmp;
            }
        }
        // 格式化
        $this->formatAmount($total, 'recharged,consume');
        foreach ($level as &$item) {
            $this->formatAmount($item, 'commissio,deposit,withdraw');
        }

        return $this->success([
            'total' => $total,
            'level' => $level
        ]);
    }
}