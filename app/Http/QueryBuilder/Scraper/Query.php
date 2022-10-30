<?php

namespace App\Http\QueryBuilder\Scraper;

use App\Http\Validation\Validation;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class Query
{
    private string $name;
    private mixed $value;

    /**
     * @throws \Exception
     */
    public function __construct(
        string $name,
        mixed $value,
        ?Validation $validation = null,
       ?\Exception $exceptionOnValidationFail = null,
    )
    {
        $this->name = $name;

        if ($validation !== null && !$validation->validate($value)) {
            throw $exceptionOnValidationFail ?? new BadRequestException("Invalid value supplied for {$name}. Please refer to the documentation: https://docs.api.jikan.moe/");
        }

        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }
}
