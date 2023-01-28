<?php

namespace App\Features;

use App\Dto\MagazineSearchCommand;
use App\Http\Resources\V4\MagazineCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @extends SearchRequestHandler<MagazineSearchCommand, MagazineCollection>
 */
final class MagazineSearchHandler extends SearchRequestHandler
{
    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return MagazineSearchCommand::class;
    }

    /**
     * @inheritDoc
     */
    protected function renderResponse(LengthAwarePaginator $paginator)
    {
        return new MagazineCollection($paginator);
    }
}
