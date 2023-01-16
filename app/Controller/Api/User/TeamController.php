<?php declare(strict_types=1);

namespace App\Controller\Api\User;

use App\Controller\AbstractController;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Psr\Http\Message\ResponseInterface;

#[Controller(prefix: '/user', server: 'apiHttp')]
class TeamController extends AbstractController
{
    /**
     * 团队
     *
     * @return ResponseInterface
     */
    #[GetMapping(path: 'team')]
    public function team(): ResponseInterface
    {

    }
}