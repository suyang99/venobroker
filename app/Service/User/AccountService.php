<?php declare(strict_types=1);

namespace App\Service\User;

use App\Interfaces\User\AccountInterface;

class AccountService implements AccountInterface
{
    /**
     * 初始化账户
     *
     * @param  int $user_id
     * @param  int $amount
     * @return bool
     */
    public function initialize(int $user_id, int $amount = 0): bool
    {

    }

    public function income(int $account, int $amount)
    {

    }

    public function expend(int $account, int $amount)
    {

    }

    public function freeze(int $account, int $amount)
    {

    }
}