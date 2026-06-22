<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

final class MaxResultsPerPageRule implements Rule
{
    private mixed $value;
    private int $fallbackLimit;

    public function __construct(?int $fallbackLimit = null)
    {
        $this->fallbackLimit = $fallbackLimit ?? max_results_per_page();
    }

    public function passes($attribute, $value): bool
    {
        $this->value = $value; // $value is being override to 25 here

        if (!is_numeric($value)) {
            return false;
        }

        if (!is_int($value)) {
            $value = intval($value);
        }

        if ($value > $this->fallbackLimit) {
            return false;
        }

        return true;
    }

    public function message(): array|string
    {
        $mrpp = max_results_per_page($this->fallbackLimit);
        return "Value {$this->value} is higher than the configured {$this->fallbackLimit} max value.";
    }
}
