<?php declare(strict_types=1);

namespace App\Middleware;

use App\Logic\Common\AuthenticationLogic;
use Hyperf\Context\Context;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var HttpResponse
     */
    protected HttpResponse $response;

    /**
     * @var AuthenticationLogic
     */
    #[Inject]
    protected ?AuthenticationLogic $auth = null;

    public function __construct(ContainerInterface $container, HttpResponse $response, RequestInterface $request)
    {
        $this->container = $container;
        $this->response  = $response;
        $this->request   = $request;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->auth->jwtAuth($this->request);

        return $handler->handle($request);
    }
}
