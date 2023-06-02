<?php

namespace App\Features;

use App\Dto\QueryMangaListOfUserCommand;
use App\Http\Resources\V4\UserProfileAnimeListCollection;
use App\Http\Resources\V4\UserProfileMangaListCollection;
use App\Http\Resources\V4\UserProfileMangaListResource;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\User\UserMangaListRequest;

/**
 * @extends RequestHandlerWithScraperCache<QueryMangaListOfUserCommand, JsonResponse>
 */
final class QueryMangaListOfUserHandler extends RequestHandlerWithScraperCache
{
    /**
     * @param QueryMangaListOfUserCommand $request
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
        if ($results->isEmpty()) {
            return new UserProfileAnimeListCollection([]);
        }

        $listResults = $results->get("manga");
        foreach ($listResults as &$result) {
            $result = (new UserProfileMangaListResource($result));
        }

        return new UserProfileMangaListCollection($listResults);
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryMangaListOfUserCommand::class;
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        /**
         * @var UserMangaListRequest $jikanParserRequest
         */
        $jikanParserRequest = $requestParams->get("jikanParserRequest");
        return $this->scraperService->findList(
            $requestFingerPrint,
            fn(MalClient $jikan, ?int $page = null) => ["anime" => $jikan->getUserMangaList($jikanParserRequest)]
        );
    }
}
