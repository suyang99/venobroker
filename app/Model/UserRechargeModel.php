<?php declare(strict_types=1);

namespace App\Model;

class UserRechargeModel extends AbstractModel
{
    protected $table = 'user_recharge';

    protected $fillable = [
        'order_num',
        'user_id',
        'channel',
        'amount',
    ];

    protected $attributes = [
        'status' => 1
    ];
}