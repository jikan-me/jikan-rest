<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Unit;

use App\Anime;
use App\Character;
use App\Club;
use App\JikanApiSearchableModel;
use App\Magazine;
use App\Manga;
use App\Person;
use App\Producers;
use App\Profile;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class SearchIndexFieldsTest extends TestCase
{
    public static function searchableModels(): array
    {
        return [
            [Anime::class, 'title'],
            [Manga::class, 'title'],
            [Character::class, 'name'],
            [Person::class, 'name'],
            [Producers::class, 'titles'],
            [Club::class, 'name'],
            [Magazine::class, 'name'],
            [Profile::class, 'username'],
        ];
    }

    #[DataProvider('searchableModels')]
    public function test_models_index_the_mal_id_as_a_searchable_string(string $class, string $titleField): void
    {
        /** @var JikanApiSearchableModel $model */
        $model = new $class;
        $model->mal_id = 42;
        $model->{$titleField} = $class === Producers::class
            ? [['title' => 'Known Title']]
            : 'Known Title';

        $searchable = $model->toSearchableArray();

        $this->assertSame('42', $searchable['mal_id_text']);
        $this->assertContains('mal_id_text', $model->typesenseQueryBy());
    }

    #[DataProvider('searchableModels')]
    public function test_models_expose_the_mal_id_text_schema_field(string $class, string $titleField): void
    {
        /** @var JikanApiSearchableModel $model */
        $model = new $class;
        $model->mal_id = 42;
        $model->{$titleField} = $class === Producers::class
            ? [['title' => 'Known Title']]
            : 'Known Title';

        $fields = collect($model->getCollectionSchema()['fields'])->pluck('name')->all();

        $this->assertContains('mal_id_text', $fields);
    }

    #[DataProvider('searchableModels')]
    public function test_models_give_the_mal_id_text_field_a_high_weight(string $class, string $titleField): void
    {
        /** @var JikanApiSearchableModel $model */
        $model = new $class;
        $model->mal_id = 42;
        $model->{$titleField} = $class === Producers::class
            ? [['title' => 'Known Title']]
            : 'Known Title';

        $weights = explode(',', (string) $model->getTypeSenseQueryByWeights());

        $this->assertSame('10', end($weights));
    }

    public function test_anime_and_manga_index_transformed_synonyms(): void
    {
        foreach ([Anime::class, Manga::class] as $class) {
            /** @var JikanApiSearchableModel $model */
            $model = new $class;
            $model->mal_id = 1;
            $model->titles = [
                ['type' => 'Default', 'title' => 'Example'],
                ['type' => 'Synonym', 'title' => 'Example: The Animation'],
            ];

            $searchable = $model->toSearchableArray();

            $this->assertSame(['Example: The Animation'], $searchable['title_synonyms']);
            $this->assertSame(['Example  The Animation'], $searchable['title_synonyms_transformed']);
        }
    }
}
