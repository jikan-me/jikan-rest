<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Integration;

use App\Enums\AnimeOrderByEnum;
use App\Enums\MangaTypeEnum;
use Tests\TestCase;

/**
 * Enum validation failures must list the accepted values, not leak PHP class names
 * ("The order by field is not a valid App\Enums\AnimeOrderByEnum.").
 */
final class EnumValidationMessageTest extends TestCase
{
    public function test_order_by_message_lists_valid_values(): void
    {
        $response = $this->get('/v4/anime?order_by=bogus')
            ->assertStatus(400)
            ->assertJsonPath('type', 'ValidationException');

        $expected = 'The order by must be one of: '.implode(', ', AnimeOrderByEnum::toValues()).'.';
        $this->assertSame([$expected], $response->json('messages.order_by'));
        $this->assertStringNotContainsString('App\\', (string) $response->getContent());
        $this->assertStringContainsString('mal_id', $expected);
    }

    public function test_other_enum_parameters_list_valid_values(): void
    {
        $response = $this->get('/v4/manga?type=bogus')->assertStatus(400);

        $this->assertSame(
            ['The type must be one of: '.implode(', ', MangaTypeEnum::toValues()).'.'],
            $response->json('messages.type')
        );
        $this->assertStringNotContainsString('App\\', (string) $response->getContent());
    }
}
