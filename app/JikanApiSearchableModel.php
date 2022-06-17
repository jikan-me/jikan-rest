<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;
use Typesense\LaravelTypesense\Interfaces\TypesenseDocument;

abstract class JikanApiSearchableModel extends JikanApiModel implements TypesenseDocument
{
    use JikanSearchable;

    /**
     * @return string[]
     */
    public abstract function typesenseQueryBy(): array;

    /**
     * The Typesense schema to be created.
     *
     * @return array
     */
    public function getCollectionSchema(): array
    {
        return [
            'name' => $this->searchableAs(),
            'fields' => [
                [
                    'name' => '.*',
                    'type' => 'auto',
                ]
            ]
        ];
    }

    /**
     * Get the name of the index associated with the model.
     *
     * @return string
     */
    public function searchableAs(): string
    {
        return strtolower($this->table) . '_index';
    }

    /**
     * Get the value used to index the model.
     *
     * @return mixed
     */
    public function getScoutKey(): mixed
    {
        return $this->mal_id;
    }

    /**
     * Get the key name used to index the model.
     *
     * @return mixed
     */
    public function getScoutKeyName(): mixed
    {
        return 'mal_id';
    }
}
