<?php declare(strict_types=1);

namespace App\Model;

use HyperfExt\Jwt\Contracts\JwtSubjectInterface;

class UserModel extends AbstractModel implements JwtSubjectInterface
{
    protected $table = 'user';

    protected $fillable = ['mobile','email','full_name','password','salt'];

    protected $attributes = [
        'status' => 1
    ];

    public function getJwtIdentifier()
    {
        return $this->getKey();
    }

    public function getJwtCustomClaims(): array
    {
        return ['guard' => 'api'];
    }
}