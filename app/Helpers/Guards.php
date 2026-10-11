<?php
namespace App\Helpers;

use MongoDB\Laravel\Eloquent\Model;

abstract class Guards
{
    /**
     * @throws \InvalidArgumentException
     */
    static function shouldBeMongoDbModel(object|string $modelClass): void
    {
        if (!in_array(Model::class, class_parents($modelClass))) {
            throw new \InvalidArgumentException("$modelClass should inherit from \MongoDB\Laravel\Eloquent\Model.");
        }
    }
}
