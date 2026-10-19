<?php

namespace Tests\Unit;

use App\Dto\QueryAnimeListOfUserCommand;
use App\Dto\QueryMangaListOfUserCommand;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Regression: `Sometimes, Required` on the date-range fields made them mandatory
 * (400 on every unfiltered list) and left `$status` uninitialised when a range
 * was supplied (500). Found by crawling every route against the real controllers.
 */
final class UserListDtoTest extends TestCase
{
    public function testAnimeListWithoutFiltersValidates(): void
    {
        $cmd = QueryAnimeListOfUserCommand::validateAndCreate(['username' => 'Xinil', 'page' => 1]);

        $this->assertSame('Xinil', $cmd->username);
        $this->assertNotNull($cmd->toJikanParserRequest());
    }

    public function testAnimeListWithRangeBuildsRequestWithDefaultStatus(): void
    {
        $cmd = QueryAnimeListOfUserCommand::validateAndCreate([
            'username' => 'Xinil', 'page' => 1, 'aired_from' => '1990-01-01', 'aired_to' => '2030-01-01',
        ]);

        $this->assertNotNull($cmd->toJikanParserRequest());
    }

    public function testMangaListWithRangeBuildsRequest(): void
    {
        $cmd = QueryMangaListOfUserCommand::validateAndCreate([
            'username' => 'Xinil', 'page' => 1, 'published_from' => '1990-01-01', 'published_to' => '2030-01-01',
        ]);

        $this->assertNotNull($cmd->toJikanParserRequest());
    }

    public function testInvertedRangeIsStillRejected(): void
    {
        $this->expectException(ValidationException::class);

        QueryAnimeListOfUserCommand::validateAndCreate([
            'username' => 'Xinil', 'page' => 1, 'aired_from' => '2030-01-01', 'aired_to' => '1990-01-01',
        ]);
    }
}
