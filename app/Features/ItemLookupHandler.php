<?php

namespace App\Features;

use App\Contracts\CachedScraperService;
use App\Contracts\RequestHandler;
use App\Dto\LookupDataCommand;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @template TRequest of LookupDataCommand
 * @template TResponse of ResourceCollection|JsonResource|JsonResponse|Response
 *
 * @implements RequestHandler<TRequest, TResponse>
 */
abstract class ItemLookupHandler implements RequestHandler
{
    public function __construct(protected readonly CachedScraperService $scraperService) {}

    /**
     * @param  TRequest|LookupDataCommand<TResponse>  $request
     * @return TResponse
     *
     * @throws NotFoundHttpException
     */
    public function handle($request)
    {
        $requestFingerprint = $request->getFingerPrint();
        $results = $this->scraperService->find($request->id, $requestFingerprint);

        $resource = $this->resource($results);

        return $resource->response()->addJikanCacheFlags($requestFingerprint, $results);
    }

    abstract protected function resource(CachedData $results): JsonResource;
}
