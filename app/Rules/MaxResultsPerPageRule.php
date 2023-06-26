<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\App;

final class MaxResultsPerPageRule implements Rule
{
    private mixed $value;
    private int $fallbackLimit;

    public function __construct($fallbackLimit = 25)
    {
        $this->fallbackLimit = $fallbackLimit;
    }

    public function passes($attribute, $value): bool
    {
        $this->value = $value;

        if (!is_numeric($value)) {
            return false;
        }

        if (!is_int($value)) {
            $value = intval($value);
        }

        if ($value > $this->maxResultsPerPage()) {
            return false;
        }

        return true;
    }

    public function message(): array|string
    {
        return "Value {$this->value} is higher than the configured '{$this->maxResultsPerPage()}' max value.";
    }

    private function maxResultsPerPage(): int
    {
        return (int) App::make("jikan-config")->maxResultsPerPage($this->fallbackLimit);
    }
}
