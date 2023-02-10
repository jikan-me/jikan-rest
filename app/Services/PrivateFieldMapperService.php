<?php

namespace App\Services;

interface PrivateFieldMapperService
{
    public function map($instance, array $values): mixed;
}
