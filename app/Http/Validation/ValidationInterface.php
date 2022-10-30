<?php

namespace App\Http\Validation;

interface ValidationInterface
{
    public function validate(mixed $value):bool;
}
