<?php declare(strict_types=1);

namespace App\Traits;

trait ModelTraits
{
    protected function getByPk(array $maps, bool $for_update = false)
    {
        $model = self::query();
        if ($maps) {
            $model->where($maps);
        }
        if ($for_update) {
            $model->lockForUpdate();
        }
    }
}