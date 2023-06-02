<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface GenreRepository extends Repository
{
    public function genres(): Collection;

    public function getExplicitItems(): Collection;

    public function getThemes(): Collection;

    public function getDemographics(): Collection;

    public function all(): Collection;
}
