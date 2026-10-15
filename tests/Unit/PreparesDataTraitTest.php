<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Unit;

use App\Dto\Concerns\HasLimitParameter;
use App\Dto\Concerns\HasPageParameter;
use App\Dto\Concerns\PreparesData;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Tests\TestCase;

final class PreparesDataFixture extends Data
{
    use HasLimitParameter, HasPageParameter, PreparesData;

    public string|Optional $filter;
}

final class PreparesDataTraitTest extends TestCase
{
    public static function paramsDataProvider(): array
    {
        return [
            'limit = empty, page = empty, filter = empty' => [['limit' => '', 'page' => '', 'filter' => ''], []],
            'limit = 1, page = empty, filter = empty' => [['limit' => '1', 'page' => '', 'filter' => ''], ['limit' => 1]],
            'limit = 1, page = 2, filter = empty' => [
                ['limit' => '1', 'page' => '2', 'filter' => ''],
                ['limit' => 1, 'page' => 2],
            ],
            'limit = 1, page = 2, filter = somefilter' => [
                ['limit' => '1', 'page' => '2', 'filter' => 'somefilter'],
                ['limit' => 1, 'page' => 2, 'filter' => 'somefilter'],
            ],
        ];
    }

    #[DataProvider('paramsDataProvider')]
    public function testShouldIgnoreEmptyParams($actual, $expected)
    {
        // Spatie Laravel Data v4's prepareForPipeline() operates on arrays (not
        // Collections like v3), so adapt the result for the loose assertions.
        $sut = collect(PreparesDataFixture::prepareForPipeline($actual));

        $this->assertCollectionsStrictlyEqual(collect($expected), $sut);
    }
}
