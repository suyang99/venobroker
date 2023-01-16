<?php declare(strict_types=1);

namespace App\Common;

use Hyperf\Di\Annotation\Inject;
use HyperfExt\Jwt\Contracts\JwtFactoryInterface;
use HyperfExt\Jwt\Contracts\ManagerInterface;

class Jwt
{
    #[Inject]
    protected ?ManagerInterface $manager = null;

    #[Inject]
    protected ?JwtFactoryInterface $factory = null;


    protected \HyperfExt\Jwt\Jwt $jwt;

    protected function __construct()
    {
        $this->jwt = $this->factory->make();
    }

    public static function instance(): static
    {
        return new static();
    }

    public static function makeToken(mixed $params): string
    {
        return self::instance()->jwt->fromUser($params);
    }

    public static function __callStatic(string $name, array $arguments)
    {
        return self::instance()->$name(...$arguments);
    }
}