<?php declare(strict_types=1);

namespace App\Model;

class UserWithdrawModel extends AbstractModel
{
    protected $table = 'user_withdraw';

    protected $attributes = [
        'status' => 1
    ];
}