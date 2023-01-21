<?php

namespace App\Features;

use App\Contracts\CachedScraperService;
use App\Dto\LookupDataCommand;
use App\Contracts\RequestHandler;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @template TRequest of LookupDataCommand<TResponse>
 * @template TResponse of ResourceCollection|JsonResource|Response
 * @implements RequestHandler<TRequest, TResponse>
 */
abstract class ItemLookupHandler implements RequestHandler
{
    public function __construct(protected readonly CachedScraperService $scraperService)
    {
    }

    /**
     * @param TRequest|LookupDataCommand<TResponse> $request
     * @return TResponse
     * @throws NotFoundHttpException
     */
    public function handle($request)
    {
        $requestFingerprint = $request->getFingerPrint();
        $results = $this->scraperService->find($request->id, $requestFingerprint);

        $resource = $this->resource($results->collect());
        return $resource->response()->addJikanCacheFlags($requestFingerprint, $results);
    }

    protected abstract function resource(Collection $results): JsonResource;
}
