<?php declare(strict_types=1);

namespace App\Model;

use App\Common\Logger;
use Hyperf\Utils\Contracts\Arrayable;
use Throwable;

class ArticleModel extends AbstractModel
{
    protected $table = 'article';
}