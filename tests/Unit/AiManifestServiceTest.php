<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Unit;

use App\Services\AI\AiManifestService;
use PHPUnit\Framework\TestCase;

final class AiManifestServiceTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function spec(): array
    {
        return ['paths' => [
            '/anime' => ['get' => [
                'operationId' => 'getAnimeSearch',
                'summary' => 'Anime search',
                'description' => 'Searches the catalog.',
                'tags' => ['anime'],
                'parameters' => [
                    ['name' => 'q', 'in' => 'query', 'description' => 'Query', 'schema' => ['type' => 'string']],
                    ['name' => 'type', 'in' => 'query', 'schema' => ['type' => 'string', 'enum' => ['tv', 'movie']]],
                    ['name' => 'page', 'in' => 'query', 'required' => true, 'schema' => ['type' => 'integer']],
                ],
            ]],
            '/random/anime' => ['get' => [
                'operationId' => 'getRandomAnime',
                'summary' => 'Random anime',
                'description' => '',
                'tags' => ['random'],
            ]],
        ]];
    }

    /**
     * @return array<string, mixed>
     */
    private function tool(string $name): array
    {
        $out = (new AiManifestService)->fromSpec($this->spec());

        foreach ($out['tools'] as $tool) {
            if ($tool['function']['name'] === $name) {
                return $tool;
            }
        }

        $this->fail("tool $name not generated");
    }

    public function test_tool_parameters_are_taken_from_the_spec(): void
    {
        $parameters = $this->tool('getAnimeSearch')['function']['parameters'];

        $this->assertSame('string', $parameters['properties']['q']['type']);
        $this->assertSame(['tv', 'movie'], $parameters['properties']['type']['enum']);
        $this->assertSame('integer', $parameters['properties']['page']['type']);
        $this->assertSame(['page'], $parameters['required']);
    }

    public function test_a_tool_without_parameters_encodes_properties_as_an_object(): void
    {
        $json = json_encode($this->tool('getRandomAnime')['function']['parameters']);

        $this->assertStringContainsString('"properties":{}', $json);
    }

    public function test_functions_mirror_the_tools(): void
    {
        $out = (new AiManifestService)->fromSpec($this->spec());

        $this->assertSame(array_map(static fn (array $t) => $t['function'], $out['tools']), $out['functions']);
    }
}
