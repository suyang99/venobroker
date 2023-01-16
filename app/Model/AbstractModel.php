<?php declare(strict_types=1);

namespace App\Model;

use App\Traits\ModelTraits;
use Hyperf\DbConnection\Model\Model;
use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

abstract class AbstractModel extends Model implements CacheableInterface
{
    use Cacheable;
    use ModelTraits;

    protected $dateFormat = 'U';

    /**
     * 信息脱敏
     *
     * @param  mixed $data
     * @param  array $field 脱敏字段
     * @return void
     */
    public static function desensitization(mixed &$data, array $field = ['password', 'salt'])
    {
        if (!$field) return;
        foreach ($field as $key) {
            if (isset($data[$key])) {
                unset($data[$key]);
            }
        }
    }
}
