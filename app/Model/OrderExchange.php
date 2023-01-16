<?php declare(strict_types=1);

namespace App\Model;

class OrderExchange extends AbstractModel
{
    protected $table = 'order_exchange';

    protected $fillable = [
        'user_id',
        'goods_id',
        'order_num',
        'amount',
        'gain',
        'direction',
        'opening',
        'closing',
        'result',
        'status'
    ];
}