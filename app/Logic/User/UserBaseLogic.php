<?php declare(strict_types=1);

namespace App\Logic\User;

use App\Common\Jwt;
use App\Common\Logger;
use App\Logic\AbstractLogic;
use App\Logic\Account\AccountLogic;
use App\Model\AdminUsersModel;
use App\Model\UserModel;
use App\Service\Util\PasswordService;
use Hyperf\DbConnection\Db;
use HyperfExt\Jwt\Exceptions\TokenBlacklistedException;
use Throwable;

class UserBaseLogic extends AbstractLogic
{
    /**
     * 获取用户信息
     *
     * @param  int   $userId
     * @param  array $extend
     * @return array
     */
    public function info(int $userId, array $extend = []): array
    {
        try {
            $user = UserModel::where('id', $userId)->first();
            if (! $user) {
                return $this->error(['user.not_exist', ['field'=> 'User']], 10005);
            }
            UserModel::desensitization($user);
            return $this->success($user);
        } catch (Throwable $throwable) {
            Logger::error($throwable);
            $this->exception('message.error', 10006);
        }
    }

    /**
     * 密码登录
     *
     * @param  string $mobile
     * @param  string $password
     * @return array
     */
    public function loginByPassword(string $mobile, string $password): array
    {
        try {
            $user = UserModel::query()->where('mobile', $mobile)->first();
            if (! $user) {
                return $this->error(['user.not_exist', ['field'=> 'mobile']], 10001);
            }
            // 用户状态
            if (1 !== $user->status) {
                return $this->error('user.locked', 10002);
            }
            // 密码验证
            if (! PasswordService::checkPassword($password, $user->password, $user->salt)) {
                return $this->error('user.login.password', 10003);
            }
            // 登录成功更新登录状态
            $user->last_login  = $_SERVER['REQUEST_TIME'];
            $user->login_times = Db::raw('login_times + 1');
            $user->save();

            $token = Jwt::makeToken($user);
            return $this->success(['token'=> $token], 'user.login.success');
        } catch (Throwable $throwable) {
            Logger::error($throwable);
            $this->exception('user.login.error', 10004);
        }
    }

    /**
     * 用户注册
     *
     * @param  array $params
     * @return array
     */
    public function registerByInfo(array $params): array
    {
        Db::beginTransaction();
        try {
            // 邀请码
            $invite = (int)(! empty($params['invitation_code']) ?: 0);
            $invite = $this->parseInviteCode($invite);
            $params = array_merge($params, $invite);
            // 密码
            $params['salt']     = '';
            $params['password'] = PasswordService::makePassword($params['password'], $params['salt']);

            $user = UserModel::query()->firstOrNew(['mobile'=> $params['mobile']], $params);
            if ($user->id) {
                Db::rollBack();
                return $this->error(__('user.registration.already', ['field'=> 'The phone number']), 10011);
            }
            // 保存数据
            if (! $user->save()) {
                Db::rollBack();
                return $this->error('user.registration.fail', 10012);
            }
            // 初始化用户账户
            ['code'=> $err, 'message'=> $msg] = make(AccountLogic::class)->init($user->id);
            if ($err) {
                Db::rollBack();
                $this->error($msg, $err);
            }
            Db::commit();

            $jwt = auth()->jwt();
            // 生成TOKEN
            $token = Jwt::makeToken($user);
            return $this->success(['token'=> $token], __('user.registration.success'));
        } catch (Throwable $throwable) {
            Db::rollBack();
            Logger::error($throwable);
            $this->exception('user.registration.error', 10013);
        }
    }

    /**
     * 重置密码
     *
     * @param  array  $params
     * @param  string $token
     * @return array
     */
    public function resetPassword(array $params, string $token): array
    {
        Db::beginTransaction();
        try {
            $user = UserModel::query()
                ->where('id', auth()->userId())
                ->lockForUpdate()
                ->first();
            if (! $user) {
                Db::rollBack();
                return $this->error('user.not_exist', 10014);
            }
            // 用户状态
            if (1 !== $user->status) {
                return $this->error('user.locked', 10002);
            }
            // 验证旧密码
            $salt  = $user->salt;
            $check = PasswordService::checkPassword(
                $params['old_password'],
                $user->password,
                $salt
            );
            if (! $check) {
                Db::rollBack();
                return $this->error('user.password_wrong', 10015);
            }
            // 新密码
            $user->password = PasswordService::makePassword(
                $params['new_password'],
                $salt
            );
            // 更新
            if (! $user->save()) {
                Db::rollBack();
                $this->error('user.reset.error', 10016);
            }
        } catch (Throwable $throwable) {
            if (! $throwable instanceof TokenBlacklistedException){
                Logger::error($throwable);
                $this->exception('', 10017);
            }
        }
        $this->logout($token);
        Db::commit();
        return $this->success('', 'user.reset.success');
    }

    /**
     * 退出登录
     *
     * @param  string $token
     * @return array
     */
    public function logout(string $token): array
    {
        $jwt = auth()->jwt()->setToken($token);
        try {
            $jwt->getBlacklist()->add($jwt->getPayload());
        } catch (Throwable $throwable) {

        }
        return $this->success();
    }

    /**
     * 修改用户状态
     *
     * @param  int $id
     * @param  int $status 用户状态 0=永久封禁 1=正常 {timestamp}=禁用到期时间
     * @return void
     */
    public function changeStatus(int $id, int $status = 0)
    {
        Db::beginTransaction();
        try {

        } catch (Throwable $throwable) {
            Logger::error($throwable->getMessage());
        }
    }

    /**
     * 解析邀请码
     *
     * @param  int $code
     * @return array
     */
    protected function parseInviteCode(int $code): array
    {
        $res = [
            'sales_id'  => 0,
            'parent_id' => 0
        ];

        if (! $code) return $res;
        // 业务员邀请码
        if (999 < $code && 10000 > $code) {
            $user = AdminUsersModel::where('invitation_code', $code)->pluck('id');
            if ($user) {
                $res['sales_id'] = $user->id;
                return $res;
            }
        }
        // 用户邀请码=用户ID
        $user = UserModel::query()->where('id', $code)->pluck('sales_id,id');
        if ($user->isNotEmpty()) {
            $res['sales_id']  = $user->sales_id;
            $res['parent_id'] = $user->parent_id;
        }
        return $res;
    }
}