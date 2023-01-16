<?php declare(strict_types=1);

namespace App\Validation;

class AccountValidator extends AbstractValidation
{
    /**
     * @var array 验证规则
     */
    protected array $rules = [
        'bindBank' => [
            'bank_name'      => 'required',
            'account_holder' => 'required',
            'account_num'    => 'required',
            'ifsc'           => 'required',
        ],
        'apply' => [
            'amount' => ['required','numeric']
        ],
    ];

    /**
     * 绑定银行卡
     *
     * @param  array $data
     * @return array
     */
    public function bindBank(array $data): array
    {
        return $this->validate('bindBank', $data);
    }

    /**
     * 提现,充值申请
     *
     * @param  array $data
     * @return array
     */
    public function apply(array $data): array
    {
        return $this->validate('apply', $data);
    }
}