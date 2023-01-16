<?php declare(strict_types=1);

namespace App\Logic\Account;

use App\Logic\AbstractLogic;
use App\Model\UserAccountLogModel;
use App\Model\UserModel;
use App\Model\UserRechargeModel;
use App\Model\UserWithdrawModel;
use Hyperf\Di\Annotation\Inject;

class RecodeLogic extends AbstractLogic
{
    /**
     * @param  int   $userId
     * @param  array $params
     * @return array
     */
    public function getRecord(int $userId, array $params = []): array
    {
        $type = $params['type'] ?? '';
        if (! $type) {
            return $this->error();
        }

        $record = match ($type) {
            'recharge' => new UserRechargeModel(),
            'withdraw' => new UserWithdrawModel(),
            'award'    => new UserAccountLogModel(),
            default    => null
        };
        if (-1 === $userId) {
            $limit  = $params['limit'] ?? 10;
            $record = $record->where('status', 2)
                ->limit($limit)
                ->orderByDesc('id')
                ->get(['user_id','amount','status']);
            $users  = $record->columns('user_id');
            $users  = UserModel::whereIn('id', $users)->pluck('full_name', 'id');
            $record->each(function ($item) use ($users) {
                $item['full_name'] = $users[$item['user_id']];
                $this->formatAmount($item);
                return $item;
            });
            $record = $record->toArray();
        } else {
            $page   = $params['page'] ?? 1  ?: 1;
            $size   = $params['size'] ?? 10 ?: 10;
            $column = 'award' == $type ? ['remark', 'amount', 'updated_at'] : ['amount', 'updated_at', 'status'];
            if ('withdraw' == $type) {
                $record = $record->where('status', '<>', '0');
            }
            $record = $record->where('user_id', $userId)
                ->paginate(
                    perPage: (int)$size,
                    columns: $column,
                    page:    (int)$page
                );
            if ($record->isNotEmpty()) {
                $data = $record->items();
                foreach ($data as &$item) {
                    $this->formatAmount($item);
                }
            }

            $record = [
                'list'  => $record->items(),
                'total' => $record->total()
            ];
        }
        return $this->success($record);
    }

    /**
     * 添加记录
     *
     * @param  int    $amount
     * @param  int    $userId
     * @param  int    $type
     * @param  string $remark
     * @return bool
     */
    public function record(int $amount, int $userId, int $type, string $remark = ''): bool
    {
        $record = new UserAccountLogModel();
        $record->user_id = $userId;
        $record->amount  = $amount;
        $record->type    = $type;
        $record->remark  = $remark;
        return $record->save();
    }
}