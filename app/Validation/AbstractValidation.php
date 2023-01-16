<?php declare(strict_types=1);

namespace App\Validation;

use App\Exception\ValidationException;
use App\Traits\ArrayResultTraits;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

abstract class AbstractValidation
{
    use ArrayResultTraits;

    #[Inject]
    protected ?ValidatorFactoryInterface $validatorFactory = null;

    /**
     * @var array
     */
    protected array $rules = [];

    /**
     * @var array
     */
    protected array $message = [];

    /**
     * @var array
     */
    protected array $attributes = [];

    /**
     * @param  string $scene
     * @param  array  $data
     * @return array
     */
    protected function validate(string $scene, array $data): array
    {
        $params = [
            'data' => $data,
            'rules' => $this->rules[$scene] ?? []
        ];

        if ($this->message[$scene] ?? null) {
            $params['message'] = $this->message[$scene];
        }

        $validator = $this->validatorFactory->make(...$params);

        if ($validator->fails()) {
            throw new ValidationException($validator->errors()->first(), 1000);
        }

        return $validator->validated();
    }
}