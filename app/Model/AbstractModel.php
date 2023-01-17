<?php declare(strict_types=1);

namespace App\Model;

use App\Common\Logger;
use App\Traits\ModelTraits;
use Hyperf\DbConnection\Model\Model;
use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;
use Hyperf\Utils\Contracts\Arrayable;
use Throwable;

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

    /**
     * 获取成员
     *
     * @param  int    $primary 当前主键
     * @param  int    $depth   获取深度 0=无限深度
     * @param  array  $fields  获取字段
     * @param  bool   $parents 向上获取父级
     * @return array
     */
    public function family(int $primary, int $depth = 0, array $fields = ['*'], bool $parents = false): array
    {
        $res = [];
        try {
            $this->addFieldsPrimaryKey($fields, 'parent_id');
            $res = $this->recursive($primary, $depth, 0, $fields, $parents);
        } catch (Throwable $throwable) {
            Logger::error($throwable);
        }
        return $res;
    }

    /**
     * 递归处理
     *
     * @param  int   $id
     * @param  int   $depth  递归层级
     * @param  int   $level  当前层级
     * @param  array $fields 获取字段
     * @param  bool  $upward 递归向上
     * @return array
     */
    protected function recursive(int $id, int $depth = 0, int $level = 0, array $fields = ['*'], bool $upward = false): array
    {
        // 父ID=0直接返回
        if ($upward && !$id) {
            return [];
        }
        // 深度控制
        $depth = $depth ?: env('RECURSIVE_DEPTH', 20);
        $key   = $upward ? 'parent_id' : $this->primaryKey;
        $data  = [];
        if ($depth > $level) {
            $res = $this->newQuery();
            $res = $upward ? $res->where($this->primaryKey, $id) : $res->where('parent_id', $id);
//            $res = $res->get($fields, $upward ? 'parent_id' : $this->primaryKey)->toArray();
            $res = $res->get($fields)->toArray();var_dump(1);
            if ($res) {
                foreach ($res as $val) {
                    $val['level']     = $level + 1;
                    $data[$val[$key]] = $val;
                    $child = $this->recursive($val[$key], $depth, ++$level, $fields, $upward);
                    if ($child) {
                        $data += $child;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * 筛选字段添加主键
     *
     * @param  array|string     $fields 原始字段
     * @param array|string|null $add    添加字段
     * @return void
     */
    protected function addFieldsPrimaryKey(array|string &$fields = '*', array|string $add = null)
    {
        if ($add && is_string($add)) {
            $add = explode(',', $add);
        }

        if (is_string($fields) && '*' !== $fields) {
            $fields = explode(',', $fields);
            if (!in_array($this->primaryKey, $fields)) {
                $fields[] = $this->primaryKey;
            }
            $fields = array_merge($fields, $add);
        } elseif (is_array($fields) || $fields instanceof Arrayable) {
            if (!in_array($this->primaryKey, $fields)) {
                $fields[] = $this->primaryKey;
            }
            $fields = array_merge($fields, $add);
        }
    }
}
