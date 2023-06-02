<?php

namespace App\Features;

use App\Dto\QueryAnimeListOfUserCommand;
use App\Http\Resources\V4\UserProfileAnimeListCollection;
use App\Http\Resources\V4\UserProfileAnimeListResource;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\User\UserAnimeListRequest;

/**
 * @extends RequestHandlerWithScraperCache<QueryAnimeListOfUserCommand, JsonResponse>
 */
final class QueryAnimeListOfUserHandler extends RequestHandlerWithScraperCache
{
    /**
     * @param QueryAnimeListOfUserCommand $request
     * @return JsonResponse
     */
    public function handle($request)
    {
        $requestParams = collect(["jikanParserRequest" => $request->toJikanParserRequest()]);
        $requestFingerPrint = $request->getFingerPrint();
        $results = $this->getScraperData($requestFingerPrint, $requestParams);

        return $this->renderResponse($requestFingerPrint, $results);
    }

    public function resource(CachedData $results): JsonResource
    {
        if ($results->isEmpty() || count($results->get("anime")) === 0) {
            return new UserProfileAnimeListCollection([]);
        }

        $listResults = $results->get("anime");
        foreach ($listResults as &$result) {
            $result = (new UserProfileAnimeListResource($result));
        }

        return new UserProfileAnimeListCollection($listResults);
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryAnimeListOfUserCommand::class;
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        /**
         * @var UserAnimeListRequest $jikanParserRequest
         */
        $jikanParserRequest = $requestParams->get("jikanParserRequest");
        return $this->scraperService->findList(
            $requestFingerPrint,
            fn(MalClient $jikan, ?int $page = null) => ["anime" => $jikan->getUserAnimeList($jikanParserRequest)]
        );
    }
}
