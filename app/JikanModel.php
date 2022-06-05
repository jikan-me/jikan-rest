<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;
use Typesense\LaravelTypesense\Interfaces\TypesenseDocument;

abstract class JikanModel extends Model implements TypesenseDocument
{
    public abstract function typesenseQueryBy(): array;

    public function getCollectionSchema(): array
    {
        // TODO: Implement getCollectionSchema() method.
        return [];
    }
}
