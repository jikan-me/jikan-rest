<?php

namespace App\Http\Validation;

interface ValidationInterface
{
    /**
     * @param mixed $value
     * @return bool
     */
    public function validate(mixed $value):bool;
}
