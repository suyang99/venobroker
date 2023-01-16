<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Controller;

use App\Common\Logger;
use App\Model\Ucenter\UserModel;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;

#[Controller(prefix: '/', server: 'apiHttp')]
class IndexController extends AbstractController
{
    #[GetMapping(path: 'index')]
    public function index(): array
    {
        $user = $this->request->input('user', 'Hyperf');
        $method = $this->request->getMethod();
var_dump($_SERVER['DOCUMENT_ROOT']);
        return [
            'method' => $method,
            'message' => "Hello {$user}.",
        ];
    }

    #[GetMapping(path: 'test')]
    public function test()
    {
//        Logger::Warning('warning');
        Logger::Error('error');
//        Logger::Info('info');
//        Logger::alert('alert');
//        Logger::notice('notice');
//        Logger::debug('debug');
//        Logger::emergency('emergency');
//        Logger::critical('critical');
//        $a = UserModel::locked(1);
//        var_dump($a);
    }
}
