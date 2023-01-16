<?php declare(strict_types=1);

namespace App\Logic;

use App\Common\Logger;
use App\Common\Redis;
use App\Model\ConfigModel;
use App\Traits\ArrayResultTraits;
use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Utils\Contracts\Arrayable;
use Throwable;

abstract class AbstractLogic
{
    use ArrayResultTraits;

    /**
     * 获取系统配置
     *
     * @param  string $key
     * @param  mixed  $default
     * @param  bool   $reload
     * @return mixed
     */
    protected function getConfig(string $key = '', mixed $default = null, bool $reload = false): mixed
    {
        try {
            $configs = Redis::hGetAll('system:config');
            $res     = [];

            if ($configs || ! $reload) {
                if (isset($configs[$key])) {
                    return $configs[$key];
                }
            }
            $configs = ConfigModel::whereIn('type', ['system','all'])->get(['key','value']);
            if ($configs->isNotEmpty()) {
                foreach ($configs as $value) {
                    $res[$value['key']] = $value['value'];
                }
                Redis::hMSet('system:config', $res);
            }
            if ($key) {
                return $res[$key] ?? $default;
            } else {
                return $res;
            }
        } catch (Throwable $throwable) {
            Logger::error($throwable);
            return $default;
        }
    }

    protected function paginate(LengthAwarePaginatorInterface $data)
    {
        $res  = [
            'list'  => $data->items(),
            'total' => $data->total()
        ];
    }

    /**
     * 格式化金额输出
     *
     * @param  int    $data
     * @param  string $key  仅数组和数据集使用(必填),可以格式化多个参数,使用','分隔
     * @return void
     */
    protected function formatAmount(mixed &$data, string $key = 'amount'): void
    {
        if (is_array($data) || $data instanceof Arrayable) {
            $key = explode(',', $key);
            foreach ($key as $value) {
                $amount       = $data[$value] ?? 0;
                $data[$value] = sprintf('%.2f',$amount / 100);
            }
        } else {
            $data = sprintf('%.2f',$data / 100);
        }
    }

    /**
     * @param  array  $resource
     * @param  string $key
     * @param  array  $åprocessor
     * @return mixed
     */
    protected function getExtensionData(array &$resource, string $key, array $processor): mixed
    {
        [$logic, $action, $params] = $processor;
        $res = $logic->$action(...$params);
        if (! $res['code']) {
            $resource[$key] = $res['data'];
        }
        return $res;
    }

    /**
     * @return string
     */
    protected function makeOrderId(): string
    {
        $orderH = time();
        $orderL = (string)(microtime(true) - $orderH);
        $orderL = str_replace('0.', '', $orderL);
        $order  = [substr((string)$orderH, 1, 10), substr($orderL, 0, 7)];
        return implode('', $order);
    }
}