<?php

namespace App\Features;

use App\Dto\ClubMembersLookupCommand;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Club\UserListRequest;

/**
 * @extends RequestHandlerWithScraperCache<ClubMembersLookupCommand, JsonResponse>
 */
final class ClubMembersLookupHandler extends RequestHandlerWithScraperCache
{
    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return ClubMembersLookupCommand::class;
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $id = $requestParams->get("id");
        return $this->scraperService->findList(
            $requestFingerPrint,
            fn(MalClient $jikan, ?int $page = null) => $jikan->getClubUsers(new UserListRequest($id, $page)),
            $requestParams->get("page", 1)
        );
    }
}
