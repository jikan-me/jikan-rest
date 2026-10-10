<?php

namespace App\Concerns;

use App\Http\HttpHelper;
use Illuminate\Http\Request;
use Spatie\LaravelData\Support\Creation\ValidationStrategy;
use Spatie\LaravelData\Resolvers\DataFromSomethingResolver;
use Spatie\LaravelData\Support\Creation\CreationContext;

/**
 * Helper trait for data transfer objects
 *
 * Ref: https://spatie.be/docs/laravel-data/v2/as-a-data-transfer-object/request-to-data-object#content-mapping-a-request-onto-a-data-object
 */
trait HasRequestFingerprint
{
    protected ?string $fingerprint = null;

    public static function fromRequest(Request $request): ?static
    {
        $creationContext = new CreationContext(
            dataClass: static::class,
            mappedProperties: [],
            currentPath: [],
            validationStrategy: ValidationStrategy::Always,
            mapPropertyNames: true,
            disableMagicalCreation: true,
            useOptionalValues: false,
            ignoredMagicalMethods: null,
            casts: null,
        );

        $result = app(DataFromSomethingResolver::class)
            ->execute(static::class, $creationContext, $request);

        $result->fingerprint = HttpHelper::resolveRequestFingerprint($request);
        return $result;
    }

    public function getFingerPrint(): string
    {
        return $this->fingerprint;
    }
}