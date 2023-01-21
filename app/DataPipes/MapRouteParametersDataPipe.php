<?php

namespace App\DataPipes;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Support\DataClass;

class MapRouteParametersDataPipe implements \Spatie\LaravelData\DataPipes\DataPipe
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
