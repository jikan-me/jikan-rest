<?php

namespace App\Features;

use App\Concerns\ScraperResultCache;
use App\Contracts\DataRequest;
use App\Contracts\Repository;
use App\Contracts\RequestHandler;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @template TRequest of DataRequest<TResponse>
 * @template TResponse of ResourceCollection|JsonResource|Response
 * @implements RequestHandler<TRequest, TResponse>
 */
abstract class ItemLookupHandler extends Data implements RequestHandler
{
    use ScraperResultCache;

    public function __construct(protected readonly Repository $repository)
    {
    }

    /**
     * @param TRequest $request
     * @return TResponse
     * @throws NotFoundHttpException
     */
    public function handle($request)
    {
        $requestFingerprint = $request->getFingerPrint();
        $results = $this->queryFromScraperCacheById(
            $this->repository,
            $request->id,
            $request->getFingerPrint(),
        );

        $resource = $this->resource($results);
        return $this->prepareResponse($requestFingerprint, $results, $resource->response());
    }

    protected abstract function resource(Collection $results): JsonResource;
}
