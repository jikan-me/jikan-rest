<?php

namespace App\Console\Commands;

use App\Services\AI\AiManifestService;
use Illuminate\Console\Command;
use JsonException;

/**
 * Generates / verifies the committed AI artifacts from the OpenAPI spec:
 *
 *   endpoints:write   regen docs/ai/{endpoints.json,manifest.json}
 *   endpoints:check   fail if the committed artifacts are stale
 */
class EndpointsCommand extends Command
{
    protected $signature = 'endpoints:write
                            {--check : verify the committed artifacts instead of writing}
                            {--diff : endpoints:write --check — print the diff}';

    protected $description = 'Generate (write) or verify (check) the AI endpoint catalog + tool manifest from the OpenAPI spec';

    public function handle(AiManifestService $service): int
    {
        $specPath = storage_path('api-docs/api-docs.json');
        if (! is_file($specPath)) {
            $this->error('OpenAPI spec missing — run php artisan l5-swagger:generate first.');

            return self::FAILURE;
        }
        try {
            $spec = json_decode((string) file_get_contents($specPath), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->error("Spec at {$specPath} is not valid JSON: {$e->getMessage()}");

            return self::FAILURE;
        }

        $data = $service->fromSpec($spec);
        $dir = base_path('docs/ai');
        $files = [
            'endpoints.json' => static fn (): array => [
                'schema_version' => AiManifestService::SCHEMA_VERSION,
                'source' => '/v4/docs/openapi',
                'endpoints' => $data['endpoints'],
            ],
            'manifest.json' => static fn (): array => [
                'schema_version' => AiManifestService::SCHEMA_VERSION,
                'openapi' => '/v4/docs/openapi',
                'tools' => $data['tools'],
                'functions' => $data['functions'],
            ],
        ];

        if ($this->option('check')) {
            return $this->check($files, $dir);
        }

        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            $this->error("Cannot create {$dir}");

            return self::FAILURE;
        }
        foreach ($files as $name => $build) {
            file_put_contents($dir.'/'.$name, $this->encode($build()));
            $this->line("wrote {$dir}/{$name}");
        }

        return self::SUCCESS;
    }

    /** @param  array<string, callable>  $builders */
    private function check(array $builders, string $dir): int
    {
        $drift = 0;
        foreach ($builders as $name => $build) {
            $want = $this->encode($build());
            $path = $dir.'/'.$name;
            if (! is_file($path) || file_get_contents($path) !== $want) {
                $drift++;
                $this->error("STALE: {$path} — run `composer endpoints:write` and commit the result.");
                if ($this->option('diff') && is_file($path)) {
                    $this->line($this->diffLines((string) file_get_contents($path), $want, 12));
                }
            } else {
                $this->line("ok: {$path}");
            }
        }

        return $drift === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function encode(array $data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n";
    }

    private function diffLines(string $old, string $new, int $limit): string
    {
        $a = explode("\n", $old);
        $b = explode("\n", $new);
        $n = 0;
        $out = [];
        foreach (array_diff($b, $a) as $line) {
            $out[] = "+ {$line}";
            if (++$n >= $limit) {
                break;
            }
        }

        return implode("\n", $out);
    }
}