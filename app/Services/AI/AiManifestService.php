<?php

namespace App\Services\AI;

use Illuminate\Support\Arr;

/**
 * Generates deterministic, machine-readable artifacts from the regenerated
 * OpenAPI spec (storage/api-docs/api-docs.json):
 *
 *  - the endpoint catalog (docs/ai/endpoints.json)
 *  - a function-calling tool manifest (docs/ai/manifest.json, OpenAI-style
 *    `tools` + legacy `functions`), used by AI agents without an MCP client.
 *
 * Both outputs are deterministic (no timestamps) so they can be committed as
 * canonical artifacts and diff-guarded in CI (composer endpoints:check).
 */
final class AiManifestService
{
    public const SCHEMA_VERSION = '1.0';

    /**
     * @param  array<string, mixed>  $spec  Decoded OpenAPI document.
     * @return array{endpoints: array<int, array<string, mixed>>, tools: array<int, array<string, mixed>>, functions: array<int, mixed>}
     */
    public function fromSpec(array $spec): array
    {
        $endpoints = $this->endpoints($spec);

        return [
            'endpoints' => $endpoints,
            'tools' => $this->tools($endpoints),
            'functions' => array_map(
                static fn (array $tool) => $tool['function'],
                $this->tools($endpoints)
            ),
        ];
    }

    /**
     * Deterministic endpoint list (sorted by path, then method).
     *
     * @param  array<string, mixed>  $spec  Decoded OpenAPI document.
     * @return array<int, array{method: string, path: string, operationId: string, summary: string, description: string, tags: array<int, string>}>
     */
    public function endpoints(array $spec): array
    {
        $out = [];
        foreach (Arr::get($spec, 'paths', []) as $path => $item) {
            foreach ($item as $method => $op) {
                if (! in_array($method, ['get', 'post', 'put', 'patch', 'delete'], true) || ! is_array($op)) {
                    continue;
                }
                $out[] = [
                    'method' => strtoupper($method),
                    'path' => $path,
                    'operationId' => (string) Arr::get($op, 'operationId', ''),
                    'summary' => (string) Arr::get($op, 'summary', ''),
                    'description' => (string) Arr::get($op, 'description', ''),
                    'tags' => array_values(array_map('strval', Arr::get($op, 'tags', []))),
                ];
            }
        }
        usort($out, static fn (array $a, array $b): int => [$a['path'], $a['method']] <=> [$b['path'], $b['method']]);

        return $out;
    }

    /**
     * OpenAI-style function-calling tool definitions, derived from the spec's
     * path/query parameters. Names are operationIds; descriptions join the
     * summary + description; inputs are minimal JSON-schema (type/enum).
     *
     * @param  array<int, array{method: string, path: string, operationId: string, summary: string, description: string, tags: array<int, string>}>  $endpoints
     * @param  array<string, mixed>  $spec  Decoded OpenAPI document (used for parameter lookup).
     * @return array<int, array{type: string, function: array<mixed>}>
     */
    public function tools(array $endpoints, array $spec = []): array
    {
        $paths = Arr::get($spec, 'paths', []);
        $tools = [];
        foreach ($endpoints as $ep) {
            $name = $ep['operationId'];
            if ($name === '' || preg_match('/^[a-zA-Z0-9_.:-]+$/', $name) !== 1) {
                continue;
            }
            $desc = $ep['summary'];
            if ($ep['description'] !== '') {
                $desc .= ($desc === '' ? '' : "\n\n").$ep['description'];
            }
            $desc = str_replace('**', '', $desc);  // plain text for tool calls

            $properties = [];
            $required = [];
            $op = $paths[$ep['path']][strtolower($ep['method'])] ?? [];
            foreach (is_array($op) ? Arr::get($op, 'parameters', []) : [] as $param) {
                if (! is_array($param)) {
                    continue;
                }
                $key = (string) Arr::get($param, 'name', '');
                if ($key === '') {
                    continue;
                }
                $schema = is_array(Arr::get($param, 'schema')) ? $param['schema'] : [];
                $prop = [
                    'type' => $this->schemaType($schema),
                    'description' => (string) Arr::get($param, 'description', ''),
                ];
                if (($enum = Arr::get($schema, 'enum')) !== null) {
                    $prop['enum'] = array_values(array_map('strval', (array) $enum));
                }
                if ((bool) Arr::get($param, 'required')) {
                    $required[] = $key;
                }
                $properties[$key] = $prop;
            }

            $tools[] = [
                'type' => 'function',
                'function' => [
                    'name' => $name,
                    'description' => trim($desc),
                    'parameters' => [
                        'type' => 'object',
                        'properties' => $properties,
                        'required' => array_values(array_unique($required)),
                    ],
                ],
            ];
        }

        return $tools;
    }

    /**
     * @param  array<string, mixed>  $schema
     */
    private function schemaType(array $schema): string
    {
        $type = (string) Arr::get($schema, 'type', 'string');

        return match ($type) {
            'integer', 'number', 'boolean' => $type,
            'array' => 'array',
            default => 'string',
        };
    }
}