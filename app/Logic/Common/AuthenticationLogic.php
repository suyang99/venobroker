<?php declare(strict_types=1);

namespace App\Logic\Common;

use App\Logic\AbstractLogic;
use App\Model\UserModel;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use HyperfExt\Jwt\Exceptions\TokenExpiredException;
use HyperfExt\Jwt\Jwt;
use HyperfExt\Jwt\JwtFactory;
use Throwable;

class AuthenticationLogic extends AbstractLogic
{
    /**
     * @var JwtFactory|null
     */
    #[Inject]
    protected ?JwtFactory $jwtFactory = null;

    /**
     * @var array 不需要验证路由
     */
    protected array $nonVerification = [
        'user/login',
        'user/logout',
        'user/register',
        'common/',
    ];

    public function jwtAuth(RequestInterface $request)
    {
        $jwt = $this->jwtFactory->make();

        try {
            if ($this->parseNonVerification($request->getPathInfo())) {
                return;
            }

            $jwt->setToken($request->getHeaderLine('token'));
            $jwt->checkOrFail();

            $userId = $jwt->getManager()->decode($jwt->getToken())->get('sub');
            Context::set('userId', $userId);
        } catch (Throwable $throwable) {
            if (! $throwable instanceof TokenExpiredException) {
                $this->exception('message.auth.fail', 203);
            } else {
                // token 过期
                $this->exception('message.auth.expired', 204);
            }
        }

    }

    /**
     * 获取用户ID
     *
     * @return mixed
     */
    public function userId(): int
    {
        return (int)Context::get('userId');
    }

    /**
     * @return Jwt
     */
    public function jwt(): Jwt
    {
        return $this->jwtFactory->make();
    }

    /**
     * 获取用户信息
     *
     * @param  string     $key
     * @param  mixed|null $default
     * @return mixed
     */
    public function userInfo(string $key = '', mixed $default = null): mixed
    {
        if (! $userId = $this->userId()) {
            $this->exception('');
        }
        $user = UserModel::where('id', $userId)->first();
        if (! $user) {
            return $default;
        }
        UserModel::desensitization($userId);
        if (! $key) {
            return $user->toArray();
        }
        return $user[$key] ?: $default;
    }

    /**
     * 不验证授权
     *
     * @param  string $pathInfo
     * @return bool
     */
    protected function parseNonVerification(string $pathInfo): bool
    {
        $pathInfo = strtolower(ltrim($pathInfo, '/'));
        if (in_array($pathInfo, $this->nonVerification)) {
            return true;
        }
        $pathInfo = explode('/', $pathInfo);

        foreach ($this->nonVerification as $item) {
            $item = explode('/', $item);
            foreach ($item as $key=> $value) {
                if (! $value) {
                    return true;
                }
                if ($pathInfo[$key] !== $value) {
                    break;
                }
            }
        }
        return false;
    }
}