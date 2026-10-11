<?php

namespace App\Concerns;

trait FilteredByLetter
{
    /**
     * The name of the field which contains the display name of the record.
     * @var ?string
     */
    protected ?string $displayNameFieldName;

    /** @noinspection PhpUnused */
    /**
     * @param \Laravel\Scout\Builder<static>|\Illuminate\Database\Eloquent\Builder<static> $query
     * @return \Laravel\Scout\Builder<static>|\Illuminate\Database\Eloquent\Builder<static>
     */
    public function filterByLetter(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $query, string $value): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        if (empty($this->displayNameFieldName)) {
            return $query;
        }
        return $query->where($this->displayNameFieldName, "like", "{$value}%");
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
