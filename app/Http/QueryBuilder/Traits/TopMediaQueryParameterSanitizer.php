<?php
namespace App\Http\QueryBuilder\Traits;

use Illuminate\Support\Collection;

trait TopMediaQueryParameterSanitizer
{
    protected function sanitizeTopMediaParameters(Collection $parameters): Collection
    {
        $unwanted_params = ["status", "q", "letter"];
        foreach ($unwanted_params as $paramName) {
            if ($parameters->offsetExists($paramName)) {
                $parameters->offsetUnset($paramName);
            }
        }

        return $parameters;
    }
}
