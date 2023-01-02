<?php

namespace App\Features;

use App\Concerns\ScraperResultCache;
use App\Contracts\RequestHandler;
use App\Dto\UsersSearchCommand;
use App\Http\Resources\V4\ResultsResource;
use Illuminate\Http\JsonResponse;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Search\UserSearchRequest;

/**
 * @implements RequestHandler<UsersSearchCommand, JsonResponse>
 */
final class UserSearchHandler implements RequestHandler
{
    use ScraperResultCache;

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return UsersSearchCommand::class;
    }

    /**
     * @param UsersSearchCommand $request
     * @return JsonResponse
     */
    public function handle($request): JsonResponse
    {
        $requestParams = collect($request->all());
        $requestFingerPrint = $request->getFingerPrint();
        $results = $this->queryFromScraperCacheByFingerPrint(
            "users",
            $requestFingerPrint,
            fn (MalClient $jikan, int $page) => $jikan->getUserSearch((new UserSearchRequest())
                ->setQuery($requestParams->get("q"))
                ->setGender($requestParams->get("gender"))
                ->setLocation($requestParams->get("location"))
                ->setMaxAge($requestParams->get("maxAge"))
                ->setMinAge($requestParams->get("minAge"))
                ->setPage($page)),
            $requestParams->get("page")
        );

        return $this->prepareResponse($requestFingerPrint, $results, (new ResultsResource(
            $results->first()
        ))->response());
    }
}
