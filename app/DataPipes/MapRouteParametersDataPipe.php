<?php

namespace App\DataPipes;

use Illuminate\Http\Request;
use Spatie\LaravelData\DataPipes\DataPipe;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataClass;

/**
 * Maps route parameters to the data class properties if a request object payload is present.
 *
 * This makes the mapping of requests to dtos easier: The controller action only has to have the dto as argument.
 */
final class MapRouteParametersDataPipe implements DataPipe
{
    /**
     * @param  array<string, mixed>  $properties
     * @return array<string, mixed>
     */
    public function handle(mixed $payload, DataClass $class, array $properties, CreationContext $creationContext): array
    {
        if ($payload instanceof Request) {
            foreach ($class->properties as $dataProperty) {
                $routeParamVal = $payload->route($dataProperty->inputMappedName ?? $dataProperty->name);

                if (!is_null($routeParamVal)) {
                    $properties[$dataProperty->name] = $routeParamVal;
                }
            }
        }

        return $properties;
    }
}
