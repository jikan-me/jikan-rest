<?php

namespace App\Services;

interface PrivateFieldMapperService
{
    /**
     * @param  array<string, mixed>  $values
     */
    /**
     * @param  array<string, mixed>  $values
     */
    public function map($instance, array $values): mixed;
}
