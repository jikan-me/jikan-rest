<?php

namespace App\DataPipes;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\LaravelData\DataPipes\DataPipe;
use Spatie\LaravelData\Support\DataClass;

/**
 * Maps route parameters to the data class properties if a request object payload is present.
 *
 * This makes the mapping of requests to dtos easier: The controller action only has to have the dto as argument.
 */
final class MapRouteParametersDataPipe implements DataPipe
{
    public function handle(mixed $payload, DataClass $class, Collection $properties): Collection
    {
        if ($payload instanceof Request) {
            foreach ($class->properties as $dataProperty) {
                $routeParamVal = $payload->route($dataProperty->inputMappedName ?? $dataProperty->name);

                if (!is_null($routeParamVal)) {
                    $properties->put($dataProperty->name, $routeParamVal);
                }
            }
        }

        return $properties;
    }
}
