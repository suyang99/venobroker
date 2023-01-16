<?php declare(strict_types=1);

namespace App\Model;

class UserBankModel extends AbstractModel
{
    protected $table = 'user_bank';

    protected $fillable = [
        'user_id',
        'bank_name',
        'account_holder',
        'account_num',
        'ifsc'
    ];
}