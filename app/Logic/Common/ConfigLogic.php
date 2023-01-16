<?php declare(strict_types=1);

namespace App\Logic\Common;

use App\Common\Logger;
use App\Logic\AbstractLogic;
use App\Model\ArticleModel;
use App\Model\BankModel;
use App\Model\ConfigModel;
use Throwable;

class ConfigLogic extends AbstractLogic
{
    /**
     * @return array|void
     */
    public function getSiteConfig()
    {
        try {
            $config = ConfigModel::whereIn('type', ['web','all'])->get(['key', 'value', 'type']);
            if (! $config) {
                $this->exception( '',501);
            }
            $res = [];
            foreach ($config as $item) {
                $res[$item['key']] = $this->parseConfig($item['key'], $item['value']);
            }
            // 收款银行配置
            $bank = BankModel::where('status', 1)->first([
                'account_name',
                'account_num',
                'ifsc',
                'upi'
            ]);
            $res['due_bank'] = $bank;
            // banner
            $banner = ArticleModel::where('type', 2)
                ->where('status', 1)
                ->orderByDesc('id')
                ->limit(10)
                ->get(['image','image_route']);
            $res['banner'] = $banner;
            return $this->success($res);
        } catch (Throwable $throwable) {
            Logger::error($throwable);
            $this->exception('message.error', 502);
        }
    }

    /**
     * 重载配置
     *
     * @return array
     */
    public function reloadConfig(): array
    {
        $this->getConfig(reload: true);
        return $this->success();
    }

    protected function parseConfig(string $key, string $value): array|string
    {
        switch ($key) {
            case 'recharge_limit':
                $value    = explode(',', $value);
                $value[1] = $value[1] ?? 0;
                return ['min'=> $value[0], 'max'=> $value[1]];
            default:
                return $value;
        }
    }
}