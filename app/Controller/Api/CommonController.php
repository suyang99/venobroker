<?php declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Logic\Account\RecodeLogic;
use App\Logic\Common\ArticleLogic;
use App\Logic\Common\ConfigLogic;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Psr\Http\Message\ResponseInterface;

/**
 * 通用接口
 */
#[Controller(prefix: '/common', server: 'apiHttp')]
class CommonController extends AbstractController
{
    /**
     * @var ConfigLogic|null
     */
    #[Inject]
    protected ?ConfigLogic $configLogic = null;

    /**
     * @var ArticleLogic|null
     */
    #[Inject]
    protected ?ArticleLogic $articleLogic = null;

    /**
     * @var RecodeLogic|null
     */
    #[Inject]
    protected ?RecodeLogic $recodeLogic = null;

    /**
     * 获取系统配置
     *
     * @return ResponseInterface
     */
    #[GetMapping(path: 'settings')]
    public function settings(): ResponseInterface
    {
        // 公告ID
        $notice = $this->articleLogic->hasNotice();
        ['data'=> $config] = $this->configLogic->getSiteConfig();

        $data   = [
            'notice' => $notice['data']
        ];

        return $this->success(array_merge($config, $data));
    }

    /**
     * 获取公告详情
     *
     * @return ResponseInterface
     */
    #[GetMapping(path: 'notice')]
    public function notice(): ResponseInterface
    {
        $id  = (int)$this->request->query('id');
        $res = $this->articleLogic->getNoticeDetail($id);
        return $this->result(...$res);
    }

    /**
     * 获取BANNER
     *
     * @return ResponseInterface
     */
    #[GetMapping(path: 'banner')]
    public function banner(): ResponseInterface
    {
        $res = $this->articleLogic->getBanners();
        return $this->result(...$res);
    }

    /**
     * 提现记录
     *
     * @return ResponseInterface
     */
    #[GetMapping(path: 'withdraw')]
    public function withdraw(): ResponseInterface
    {
        $res = ['limit'=> 10, 'type'=> 'withdraw'];
        $res = $this->recodeLogic->getRecord(-1, $res);
        return $this->result(...$res);
    }

    /**
     * 重载配置
     *
     * @return ResponseInterface
     */
    #[GetMapping(path: 'reload_config')]
    public function reloadConfig(): ResponseInterface
    {
        $this->configLogic->reloadConfig();
        return $this->success('');
    }
}