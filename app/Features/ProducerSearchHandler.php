<?php

namespace App\Features;

use App\Dto\ProducersSearchCommand;
use App\Http\Resources\V4\ProducerCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @extends SearchRequestHandler<ProducersSearchCommand, ProducerCollection>
 */
class ProducerSearchHandler extends SearchRequestHandler
{
    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return ProducersSearchCommand::class;
    }

    /**
     * @inheritDoc
     */
    protected function renderResponse(LengthAwarePaginator $paginator)
    {
        return new ProducerCollection($paginator);
    }
}
