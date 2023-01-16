<?php declare(strict_types=1);

namespace App\Logic\Account;

use App\Common\Logger;
use App\Logic\AbstractLogic;
use App\Model\UserAccountModel;
use App\Model\UserBankModel;
use App\Model\UserRechargeModel;
use App\Model\UserWithdrawModel;
use Hyperf\DbConnection\Db;
use Throwable;

class AccountLogic extends AbstractLogic
{
    /**
     * 初始化账户
     *
     * @param  int $userId
     * @return array
     */
    public function init(int $userId): array
    {
        try {
            $account = UserAccountModel::query()->firstOrCreate(['id'=> $userId]);
            return $this->success($account->toArray());
        } catch (Throwable $throwable) {
            Logger::error($throwable);
            return $this->error('user.account.init_error', 10100);
        }
    }

    /**
     * 获取账户信息
     *
     * @param  int   $userId
     * @param  array $extend 扩展信息
     * @return array
     */
    public function info(int $userId, array $extend = []): array
    {
        try {
            $account = UserAccountModel::where('id', $userId)->first();
            if (! $account) {
                $account = $this->init($userId);
            }
            $account = is_array($account) ? $account : $account->toArray();
            $this->formatAmount($account, 'balance,frozen');
            // 扩展信息
            if ($extend) {
                foreach ($extend as $value) {
                    ['code'=> $code, 'data'=> $data] = $this->bankInfo($userId);
                    if (! $code) {
                        $account[$value] = $data;
                    }
                }
            }
            return $this->success($account);
        } catch (Throwable $throwable) {
            Logger::error($throwable);
            $this->exception( code: 10101);
        }
    }

    /**
     * 银行卡信息
     *
     * @param  int $userId
     * @return array
     */
    public function bankInfo(int $userId): array
    {
        try {
            $bank = UserBankModel::where('user_id', $userId)->first([
                'bank_name',
                'account_holder',
                'account_num',
                'ifsc'
            ]);
            return $this->success($bank ?: []);
        } catch (Throwable $throwable) {
            Logger::error($throwable);
            $this->exception(code:  10103);
        }
    }

    /**
     * 绑定银行卡
     *
     * @param  int   $userId
     * @param  array $params
     * @return array
     */
    public function bindBank(int $userId, array $params): array
    {
        Db::beginTransaction();
        try {
            $bank = UserBankModel::query()
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->firstOrCreate(
                    ['user_id'=> $userId],
                    array_merge($params, ['user_id'=> $userId])
                );
            Db::commit();
            return $this->success($bank);
        } catch (Throwable $throwable) {
            Db::rollBack();
            Logger::error($throwable);
            $this->exception(code: 10104);
        }
    }

    /**
     * 充值申请
     *
     * @param  int $userId
     * @param  int $amount
     * @return array
     */
    public function recharge(int $userId, int $amount): array
    {
        try {
            $model = new UserRechargeModel();
            $model->user_id   = $userId;
            $model->order_num = $this->makeOrderId();
//            $model->channel = auth()->userInfo('sales_id');
            $model->amount    = $amount;
            if (! $model->save()) {
                return $this->error('', 10201);
            }
            return $this->success();
        } catch (Throwable $throwable) {
            Logger::error($throwable);
            $this->exception(code: 10200);
        }
    }

    /**
     * 提现申请
     *
     * @param  int $userId     用户ID
     * @param  int $amount     金额
     * @param  int $chargeType 手续费扣除方式 1=从提现金额扣除 2=从余额扣除
     * @return array
     */
    public function withdraw(int $userId, int $amount, int $chargeType = 1): array
    {
        $charge  = (int)$this->getConfig('withdraw_charge', 0);
        $low     = (int)$this->getConfig('withdraw_limit', 0);
        // 最低提现金额
        if ($low * 100 > $amount) {
            return $this->error(['user.withdraw.low_limit', ['amount'=> $low/100]], 10305);
        }
        // 判断手续费
        if ($charge) {
            $charge = ceil($amount * ($charge/100));
        }
        $total   = $amount + $charge;
        Db::beginTransaction();
        try {
            $account = UserAccountModel::query()->lockForUpdate()->find($userId);
            // 账户不存在
            if (! $account) {
                Db::rollBack();
                return $this->error(['user.not_exist', ['field'=> 'account']], 10301);
            }
            // 余额不足
            if ($total > $account->balance) {
                Db::rollBack();
                return $this->error('user.account.balance_ins', 10302);
            }

            $account->balance = Db::raw("balance - {$total}");
            $account->frozen  = Db::raw("frozen + {$charge}");
            // 更新
            $res = $account->save();
            if (! $res) {
                Db::rollBack();
                return $this->error('user.withdraw.fail', 10303);
            }
            // 创建填写申请
            $res = new UserWithdrawModel();
            $res->user_id   = $userId;
            $res->order_num = $this->makeOrderId();
            $res->amount    = $amount;
            $res->charge    = $charge;
            if (! $res->save()) {
                Db::rollBack();
                return $this->error('user.withdraw.fail', 10304);
            }
            Db::commit();
            return $this->success('','user.withdraw.success');
        } catch (Throwable $throwable) {
            Db::rollBack();
            Logger::error($throwable);
            $this->exception(code: 10300);
        }
    }

    public function consume(int $userId, int $amount)
    {

    }
}