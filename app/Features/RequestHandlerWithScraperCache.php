<?php

namespace App\Features;

use App\Contracts\CachedScraperService;
use App\Contracts\RequestHandler;
use App\Contracts\DataRequest;
use App\Http\Resources\V4\ResultsResource;
use App\Support\CachedData;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

/**
 * @template TRequest of DataRequest<TResponse>
 * @template TResponse of ResourceCollection|JsonResource|Response
 * @implements RequestHandler<TRequest, TResponse>
 */
abstract class RequestHandlerWithScraperCache implements RequestHandler
{
    public function __construct(protected readonly CachedScraperService $scraperService)
    {
    }

    /**
     * @inheritDoc
     */
    public function handle($request)
    {
        $requestParams = collect($request->all());
        $requestFingerPrint = $request->getFingerPrint();
        $results = $this->getScraperData($requestFingerPrint, $requestParams);

        return $this->renderResponse($requestFingerPrint, $results);
    }

    protected abstract function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData;

    protected function resource(Collection $results): JsonResource
    {
        return new ResultsResource(
            $results->first()
        );
    }

    /**
     * @param string $requestFingerPrint
     * @param CachedData $results
     * @return TResponse
     */
    protected function renderResponse(string $requestFingerPrint, CachedData $results)
    {
        $finalResults = $results->collect();
        $response = $this->resource($finalResults)->response();
        return $this->scraperService->augmentResponse($response, $requestFingerPrint, $results);
    }
}
