<?php
/** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\Unit;
use App\Dto\Concerns\HasLimitParameter;
use App\Dto\Concerns\HasPageParameter;
use App\Dto\Concerns\PreparesData;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Tests\TestCase;

final class PreparesDataFixture extends Data
{
    use PreparesData, HasLimitParameter, HasPageParameter;

    public string|Optional $filter;
}

final class PreparesDataTraitTest extends TestCase
{
    public function paramsDataProvider(): array
    {
        return [
            'limit = empty, page = empty, filter = empty' => [['limit' => '', 'page' => '', 'filter' => ''], []],
            'limit = 1, page = empty, filter = empty' => [['limit' => '1', 'page' => '', 'filter' => ''], ['limit' => 1]],
            'limit = 1, page = 2, filter = empty' => [
                ['limit' => '1', 'page' => '2', 'filter' => ''],
                ['limit' => 1, 'page' => 2]
            ],
            'limit = 1, page = 2, filter = somefilter' => [
                ['limit' => '1', 'page' => '2', 'filter' => 'somefilter'],
                ['limit' => 1, 'page' => 2, 'filter' => 'somefilter']
            ],
        ];
    }

    /**
     * @dataProvider paramsDataProvider
     */
    public function testShouldIgnoreEmptyParams($actual, $expected)
    {
        $sut = PreparesDataFixture::prepareForPipeline(collect($actual));

        $this->assertCollectionsStrictlyEqual(collect($expected), $sut);
    }
}
