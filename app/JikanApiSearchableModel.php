<?php

namespace App;


/**
 * Mongo-backed searchable model base. Attributes are magic (from the collection);
 * documented below are the shared ones the app reads.
 *
 * @property int $mal_id
 */
abstract class JikanApiSearchableModel extends JikanApiModel
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
        $titleAttributeName = $this->getTitleAttributeName();

        return [
            'name' => $this->searchableAs(),
            'fields' => [
                [
                    'name' => '.*',
                    'type' => 'auto',
                ],
                [
                    'name' => $titleAttributeName,
                    'type' => 'string',
                    'sort' => true,
                    'optional' => false
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
        return strtolower($this->table) . '_index' . (app()->environment("testing") ? "_testing" : "");
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

    /**
     * Returns what weights to use on query_by fields.
     * https://typesense.org/docs/0.23.0/api/documents.html#search-parameters
     * @return string|null
     */
    public function getTypeSenseQueryByWeights(): string|null
    {
        return null;
    }

    /**
     * Returns which fields the search index should sort on when searching
     * @return array|null
     */
    public function getSearchIndexSortBy(): array|null
    {
        return null;
    }

    protected function simplifyStringForSearch($val): string
    {
        if (!$val) {
            return "";
        }
        return preg_replace("/[^[:alnum:][:space:]]/u", ' ', $val) ?? "";
    }

    public function getTitleAttributeName(): string
    {
        return 'title';
    }
}
