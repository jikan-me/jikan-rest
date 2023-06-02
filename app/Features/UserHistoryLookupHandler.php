<?php

namespace App\Features;

use App\Dto\UserHistoryLookupCommand;
use App\Http\Resources\V4\ProfileHistoryResource;
use App\Support\CachedData;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\User\UserHistoryRequest;

/**
 * @extends RequestHandlerWithScraperCache<UserHistoryLookupCommand>
 */
final class UserHistoryLookupHandler extends RequestHandlerWithScraperCache
{
    public function requestClass(): string
    {
        return UserHistoryLookupCommand::class;
    }

    protected function resource(CachedData $results): JsonResource
    {
        return new ProfileHistoryResource($results);
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $type = $requestParams->get("type");
        $username = $requestParams->get("username");
        return $this->scraperService->findList(
            $requestFingerPrint,
            fn(MalClient $jikan, ?int $page = null) => ["history" => $jikan->getUserHistory(new UserHistoryRequest(
                $username, $type
            ))]
        );
    }
}
