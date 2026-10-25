<?php

namespace Tests\Integration;

use Tests\TestCase;

/**
 * End-to-end deployment-surface coverage: the ops/health endpoints and the
 * committed deployment artifacts (Dockerfile/compose/CI/AI artifacts). Guards
 * regressions like a dropped health route, a resurrected RR_STAGE, a missing
 * generated manifest, or CI mistakes — the things that only bite on deploy.
 */
final class DeploymentArtifactsTest extends TestCase
{
    public function test_health_and_llm_surface_endpoints(): void
    {
        $this->get('/up')->assertOk()->assertSeeText('OK');

        $llms = $this->get('/llms.txt');
        $llms->assertOk();
        $this->assertStringContainsString('text/plain', (string) $llms->headers->get('content-type'));
        // response()->file() streams the body; assert the committed source instead.
        $this->assertStringStartsWith('# Jikan REST API', (string) file_get_contents(base_path('llms.txt')));

        $manifestRoute = $this->get('/v4/ai/manifest.json');
        $manifestRoute->assertOk();
        $this->assertStringContainsString('application/json', (string) $manifestRoute->headers->get('content-type'));
        // response()->file() streams; assert the committed artifact it serves.
        $manifest = json_decode((string) file_get_contents(base_path('docs/ai/manifest.json')), true);
        $this->assertArrayHasKey('schema_version', $manifest);
        $this->assertArrayHasKey('tools', $manifest);
        $this->assertArrayHasKey('functions', $manifest);
        $this->assertGreaterThanOrEqual(100, count($manifest['tools'] ?? []));

        $spec = $this->get('/v4/docs/openapi');
        $spec->assertOk();
        $this->assertSame('3.0.0', $spec->json('openapi') ?? '3.1.0');
    }

    public function test_dockerfile_invariants(): void
    {
        $dockerfile = file_get_contents(base_path('Dockerfile'));
        $this->assertNotFalse($dockerfile);

        $this->assertStringContainsString('php:8.4', $dockerfile, 'base image must be PHP 8.4');
        $this->assertStringContainsString('--ignore-platform-reqs', $dockerfile, 'deps stage needs platform-req bypass');
        $this->assertStringContainsString('linux-headers', $dockerfile, 'Alpine sockets build needs linux-headers');
        $this->assertStringContainsString('uname -m', $dockerfile, 'RR arch must be auto-detected, not a build arg');
        $this->assertStringNotContainsString('RR_STAGE', $dockerfile, 'the legacy RR_STAGE arg must stay gone');
        $this->assertFileExists(base_path('docker-entrypoint.sh'));
        $this->assertFileExists(base_path('docker-entrypoint.php'));
    }

    public function test_env_dist_has_no_empty_path_traps_and_docs_page_renders(): void
    {
        // The container copies .env.dist -> .env; an uncommented EMPTY
        // VIEW_COMPILED_PATH= used to cache view.compiled='' and 500 /v4/docs.
        $envDist = (string) file_get_contents(base_path('.env.dist'));
        $this->assertDoesNotMatchRegularExpression('/^VIEW_COMPILED_PATH=\s*$/m', $envDist);
        $this->assertDoesNotMatchRegularExpression('/^OCTANE_STATE_FILE=\s*$/m', $envDist);

        $original = getenv('VIEW_COMPILED_PATH');
        putenv('VIEW_COMPILED_PATH=');
        $_ENV['VIEW_COMPILED_PATH'] = $_SERVER['VIEW_COMPILED_PATH'] = '';
        try {
            $config = require base_path('config/view.php');
            $this->assertNotEmpty($config['compiled'], 'empty VIEW_COMPILED_PATH must fall back to storage/framework/views');
        } finally {
            if ($original === false) {
                putenv('VIEW_COMPILED_PATH');
                unset($_ENV['VIEW_COMPILED_PATH'], $_SERVER['VIEW_COMPILED_PATH']);
            } else {
                putenv('VIEW_COMPILED_PATH='.$original);
                $_ENV['VIEW_COMPILED_PATH'] = $_SERVER['VIEW_COMPILED_PATH'] = $original;
            }
        }

        $this->get('/v4/docs')->assertOk();
    }

    public function test_compose_files_are_sanctioned(): void
    {
        $this->assertFileExists(base_path('docker-compose.yml'));
        $compose = file_get_contents(base_path('docker-compose.yml'));
        foreach (['mongo', 'redis', 'app'] as $service) {
            $this->assertStringContainsString($service, $compose, "compose must define service {$service}");
        }
        $this->assertStringNotContainsString('RR_STAGE', $compose);

        $this->assertFileExists(base_path('docker-compose.typesense.yml'));
        $this->assertStringContainsString('typesense/typesense:', file_get_contents(base_path('docker-compose.typesense.yml')));
    }

    public function test_ci_workflows_and_ai_artifacts(): void
    {
        $ci = file_get_contents(base_path('.github/workflows/ci.yml'));
        $this->assertStringContainsString('artifact-freshness', $ci);
        // CI pins an explicit Mongo minor. It runs 7.0, not compose's 8.0: MongoDB 8.0+ refuses
        // to start on kernel >= 6.19 (SERVER-121912), which GitHub runners now ship.
        $this->assertMatchesRegularExpression('/image: mongo:\d+\.\d+\b/', $ci, 'CI Mongo must be pinned to a minor version');
        $this->assertFileDoesNotExist(base_path('.github/workflows/tests.yml'), 'legacy tests.yml must stay deleted');

        foreach (['AGENTS.md', 'CLAUDE.md', 'llms.txt', 'docs/INDEX.md',
            'docs/ai/endpoints.json', 'docs/ai/manifest.json'] as $artifact) {
            $this->assertFileExists(base_path($artifact), "deployment artifact missing: {$artifact}");
        }
    }
}
