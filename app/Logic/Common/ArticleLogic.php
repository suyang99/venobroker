<?php declare(strict_types=1);

namespace App\Logic\Common;

use App\Logic\AbstractLogic;
use App\Model\ArticleModel;

class ArticleLogic extends AbstractLogic
{
    /**
     * 判断是否有公告
     *
     * @return array
     */
    public function hasNotice(): array
    {
        $res = ArticleModel::query()
            ->where('type', 1)
            ->where('status', 1)
            ->first(['id']);
        $res = $res ? $res->id : 0;
        return $this->success($res);
    }

    /**
     * 获取公告详情
     *
     * @param  int $id
     * @return array
     */
    public function getNoticeDetail(int $id): array
    {
        $res = ArticleModel::query()->where('id', $id)->first();
        if (! $res) {
            $this->exception('message.error.not_found');
        }
        return $this->success($res);
    }

    /**
     * 获取BANNER列表
     * @return array
     */
    public function getBanners(): array
    {
        $res = ArticleModel::query()
            ->where('type', 2)
            ->where('status', 1)
            ->limit(10)
            ->get(['id', 'title', 'image', 'image_route']);
        return $this->success($res);
    }
}