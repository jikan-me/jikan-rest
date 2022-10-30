<?php

namespace App\Http\Validation;

abstract class Validation implements ValidationInterface
{
    /**
     * @var array|int|float|string
     */
    public array|int|float|string $criteria;

    /**
     * @param array|int|float|string $criteria
     */
    public function __construct(array|int|float|string $criteria) {
        $this->criteria = $criteria;
    }
}
