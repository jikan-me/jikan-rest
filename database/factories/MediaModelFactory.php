<?php

namespace Database\Factories;

use Illuminate\Support\Collection;

interface MediaModelFactory
{
    public function overrideFromQueryStringParameters(array $additionalParams, bool $doOpposite = false): self;

    /**
     * Helper function to create records in db in sequential order.
     * The records are created with an increasing value for the specified field.
     * @param string $orderByField The field to order items by. Used to generate increasing value for the field.
     * @return Collection The created items/records
     */
    public function createManyWithOrder(string $orderByField): Collection;
}
