<?php

namespace App\Casts;

use BackedEnum;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Uncastable;
use Spatie\LaravelData\Exceptions\CannotCastEnum;
use Spatie\LaravelData\Support\DataProperty;
use Throwable;

class EnumCast implements Cast
{
    public function __construct(
        protected ?string $type = null
    ) {
    }

    /**
     * @throws CannotCastEnum
     */
    public function cast(DataProperty $property, mixed $value, array $context): mixed
    {
        $type = $this->type ?? $property->type->findAcceptedTypeForBaseType(BackedEnum::class);

        if ($type === null) {
            return Uncastable::create();
        }

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            return $type::from($value);
        } catch (Throwable $e) {
            throw CannotCastEnum::create($type, $value);
        }
    }
}
