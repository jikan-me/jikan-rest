<?php

namespace App\Concerns;

use Laravel\Scout\Builder;

trait FilteredByLetter
{
    /**
     * The name of the field which contains the display name of the record.
     */
    protected ?string $displayNameFieldName;

    /** @noinspection PhpUnused */
    /**
     * @param  Builder<static>|\Illuminate\Database\Eloquent\Builder<static>  $query
     * @return Builder<static>|\Illuminate\Database\Eloquent\Builder<static>
     */
    public function filterByLetter(Builder|\Illuminate\Database\Eloquent\Builder $query, string $value): Builder|\Illuminate\Database\Eloquent\Builder
    {
        if (empty($this->displayNameFieldName)) {
            return $query;
        }

        return $query->where($this->displayNameFieldName, 'like', "{$value}%");
    }

    public function getDisplayNameFieldName(): string
    {
        return $this->displayNameFieldName;
    }

    public function displayNameFieldName(string $name): self
    {
        $this->displayNameFieldName = $name;

        return $this;
    }
}
