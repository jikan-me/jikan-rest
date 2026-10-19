<?php

namespace Tests\Integration;

use Tests\TestCase;

/**
 * A self-hosted instance must advertise itself, not the discontinued public API.
 */
final class SelfHostedUrlsTest extends TestCase
{
    public function test_root_and_v4_index_use_app_url(): void
    {
        config(['app.url' => 'https://jikan.example.test/']);

        foreach (['/', '/v4'] as $uri) {
            $this->get($uri)
                ->assertOk()
                ->assertJsonPath('production_api_url', 'https://jikan.example.test/v4/');
        }
    }

    public function test_llms_txt_base_url_uses_app_url(): void
    {
        config(['app.url' => 'https://jikan.example.test']);

        $body = $this->get('/llms.txt')->assertOk()->getContent();

        $this->assertStringContainsString('`https://jikan.example.test/v4/`', $body);
        $this->assertStringNotContainsString('{{BASE_URL}}', $body);
        $this->assertStringNotContainsString('api.jikan.moe', $body);
    }

    public function test_openapi_server_is_relative_to_the_instance(): void
    {
        $spec = json_decode((string) file_get_contents(base_path('storage/api-docs/api-docs.json')), true);

        $this->assertSame(['/v4'], array_column($spec['servers'] ?? [], 'url'));
        $this->assertStringNotContainsString('api.jikan.moe/v4', json_encode($spec));
    }
}
