<?php

namespace App\Services;

use App\Dto\QueryListOfUserCommand;
use App\Enums\UserListTypeEnum;
use Carbon\CarbonImmutable;
use Jikan\Request\User\UserAnimeListRequest;
use Jikan\Request\User\UserMangaListRequest;
use Spatie\LaravelData\Optional;

final class JikanUserListRequestMapperService
{
    public function __construct(private readonly PrivateFieldMapperService $fieldMapperService) {}

    public function map(QueryListOfUserCommand $command, UserListTypeEnum $listType): UserAnimeListRequest|UserMangaListRequest
    {
        $values = collect($command->all())->except(['username', 'page', 'status'])->toArray();

        // `status` may be an Optional or simply uninitialised (when the DTO is resolved from the
        // HTTP request without it); both mean "all". Otherwise the enum's label carries the MAL
        // status code ('watching' => '1') and the parser wants an int.
        $status = isset($command->status) && ! $command->status instanceof Optional
            ? (int) $command->status->label
            : 7;

        if (array_key_exists('sort', $values)) {
            $values['sort'] = $values['sort'] === 'asc' ? -1 : 1;
        }

        if ($listType->equals(UserListTypeEnum::anime())) {
            $rangeFrom = 'airedFrom';
            $rangeTo = 'airedTo';
            $jikanUserListRequest = new UserAnimeListRequest($command->username, $command->page, $status);
        } else {
            $rangeFrom = 'publishedFrom';
            $rangeTo = 'publishedTo';
            $jikanUserListRequest = new UserMangaListRequest($command->username, $command->page, $status);
        }

        foreach ([$rangeFrom, $rangeTo] as $rangeField) {
            if (array_key_exists($rangeField, $values)) {
                /**
                 * @var CarbonImmutable $c
                 */
                $c = $values[$rangeField];
                $values[$rangeField] = [$c->year, $c->month, $c->day];
            }
        }

        return $this->fieldMapperService->map($jikanUserListRequest, $values);
    }
}
