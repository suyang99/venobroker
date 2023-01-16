<?php declare(strict_types=1);

namespace App\Validation;

class TradeValidator extends AbstractValidation
{
    protected array $rules = [
        'purchase'  => [
            'goods_id'  => ['required', 'numeric'],
            'amount'    => ['required', 'numeric'],
            'direction' => ['required'],
//            'ts'        => ['required', 'numeric']
        ],
    ];

    public function purchase(array $data)
    {
        return $this->validate('purchase', $data);
    }
}