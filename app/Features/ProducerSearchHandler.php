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
     * {@inheritDoc}
     */
    public function requestClass(): string
    {
        return ProducersSearchCommand::class;
    }

    /**
     * @param  LengthAwarePaginator<int, mixed>  $paginator
     */
    protected function renderResponse(LengthAwarePaginator $paginator): ProducerCollection
    {
        return new ProducerCollection($paginator);
    }
}
