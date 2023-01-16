<?php declare(strict_types=1);

namespace App\Controller\Api\User;

use App\Controller\AbstractController;
use App\Logic\User\UserBaseLogic;
use App\Validation\UserValidator;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Psr\Http\Message\ResponseInterface;

#[Controller(prefix: '/user', server: 'apiHttp')]
class UserController extends AbstractController
{
    #[Inject]
    protected ?UserValidator $userValidator = null;

    #[Inject]
    protected ?UserBaseLogic $userBaseLogic = null;

    /**
     * 用户详情
     *
     * @return ResponseInterface
     */
    #[GetMapping(path: 'info')]
    public function info(): ResponseInterface
    {
        $res = $this->userBaseLogic->info(auth()->userId());
        return $this->result(...$res);
    }

    /**
     * 登录
     *
     * @return ResponseInterface
     */
    #[PostMapping(path: 'login')]
    public function login(): ResponseInterface
    {
        $params = $this->request->post();
        $params = $this->userValidator->login($params);
        $res    = $this->userBaseLogic->loginByPassword(...$params);
        return $this->result(...$res);
    }

    /**
     * 登出
     *
     * @return ResponseInterface
     */
    #[GetMapping(path: 'logout')]
    public function logout(): ResponseInterface
    {
        $res = $this->userBaseLogic->logout($this->request->getHeaderLine('token'));
        return $this->result(...$res);
    }

    /**
     * 注册
     *
     * @return ResponseInterface
     */
    #[PostMapping(path: 'register')]
    public function register(): ResponseInterface
    {
        $params = $this->request->post();
        $params = $this->userValidator->registration($params);
        $res    = $this->userBaseLogic->registerByInfo($params);
        return $this->result(...$res);
    }

    /**
     * 修改密码
     *
     * @return ResponseInterface
     */
    #[PostMapping(path: 'reset_password')]
    public function resetPassword(): ResponseInterface
    {
        $params = $this->request->post();
        $params = $this->userValidator->reset($params);
        $token  = $this->request->getHeaderLine('token');
        $res    = $this->userBaseLogic->resetPassword($params, $token);
        return $this->result(...$res);
    }
}