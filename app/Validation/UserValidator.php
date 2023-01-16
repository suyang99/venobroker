<?php declare(strict_types=1);

namespace App\Validation;

use Hyperf\Validation\Rule;

class UserValidator extends AbstractValidation
{
    /**
     * @var array 验证规则
     */
    protected array $rules = [
        'login'  => [
            'mobile'   => ['required'],
            'password' => ['required', 'between:6,20']
        ],
        'signUp' => [
            'mobile'   => [
                'required'
            ],
            'full_name' => ['required'],
            'password'  => ['required', 'between:6,20'],
            'email'     => ['required']
        ],
        'reset' => [
            'old_password' => ['required', 'between:6,20'],
            'new_password' => ['required', 'between:6,20'],
        ],
    ];

    /**
     * 登录
     *
     * @param  array $data
     * @return array
     */
    public function login(array $data): array
    {
        return $this->validate('login', $data);
    }

    /**
     * 注册
     *
     * @param  array $data
     * @return array
     */
    public function registration(array $data): array
    {
        $this->rules['signUp']['mobile'][] =
            Rule::unique('user')->where(function($query) {
                $query->where('status', 1);
            });
        return $this->validate('signUp', $data);
    }

    /**
     * 重置密码
     *
     * @param  array $data
     * @return array
     */
    public function reset(array $data): array
    {
        return $this->validate('reset', $data);
    }
}