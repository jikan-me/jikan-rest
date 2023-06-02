<?php

namespace App\Features;

use App\Dto\MangaSearchCommand;
use App\Enums\MangaOrderByEnum;
use App\Http\Resources\V4\MangaCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * @extends SearchRequestHandler<MangaSearchCommand, MangaCollection>
 */
class MangaSearchHandler extends SearchRequestHandler
{
    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return MangaSearchCommand::class;
    }

    /**
     * @inheritDoc
     */
    protected function renderResponse(LengthAwarePaginator $paginator)
    {
        return new MangaCollection($paginator);
    }

    protected function prepareOrderByParam(Collection $requestData): Collection
    {
        if ($requestData->has("q") && !$requestData->has("order_by")) {
            // default order by should be popularity, as MAL seems to use this trick.
            $requestData->offsetSet("order_by", MangaOrderByEnum::popularity());
        }

        return parent::prepareOrderByParam($requestData);
    }
}
