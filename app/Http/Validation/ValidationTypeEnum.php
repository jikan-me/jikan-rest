<?php
namespace App\Http\Validation;

class ValidationTypeEnum extends Validation
{
    /**
     * @param mixed $value
     * @return bool
     */
    public function validate(mixed $value) : bool
    {
        return in_array($value, $this->criteria);
    }
}
