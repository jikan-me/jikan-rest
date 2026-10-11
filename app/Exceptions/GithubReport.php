<?php

namespace App\Exceptions;

use Composer\InstalledVersions;
use Illuminate\Http\Request;
use Predis\Connection\ConnectionException;

/**
 * Class GithubReport
 */
class GithubReport
{
    private string $name;

    private string $code;

    private string $error;

    private string $requestUri;

    private string $requestMethod;

    private string $repo;

    private string $trace;

    private string $jikanVersion;

    private string|bool $redisRunning = false;

    private string $instanceType;

    private string $phpVersion;

    /**
     * @param  \Exception  $exception
     */
    public static function make(\Throwable $exception, Request $request, ?string $repo = null): self
    {
        $report = new self;
        $report->name = \get_class($exception);
        $report->code = $exception->getCode();
        $report->error = $exception->getMessage();
        $report->trace = "{$exception->getFile()} on line {$exception->getLine()}";
        $report->repo = $repo ?? config('jikan.github_rest');
        $report->requestUri = $request->getRequestUri();
        $report->requestMethod = $request->getMethod();
        $report->jikanVersion = InstalledVersions::getVersion('jikan-me/jikan');
        $report->phpVersion = PHP_VERSION;

        $report->redisRunning = false;
        if (config('jikan.micro_caching_enabled') && strtolower(config('cache.default')) === 'redis') {
            try {
                $report->redisRunning = trim(app('redis')->ping()) === 'PONG' ? 'Connected' : 'Disconnected';
            } catch (ConnectionException $e) {
                $report->redisRunning = false;
            }
        }

        $report->instanceType = 'UNKNOWN';
        if (! app()->environment('testing')) {
            if (array_key_exists('SERVER_NAME', $_SERVER)) {
                $report->instanceType = $_SERVER['SERVER_NAME'] === 'api.jikan.moe' ? 'OFFICIAL' : 'HOSTED';
            } else {
                $report->instanceType = 'HOSTED-RR';
            }
        }

        return $report;
    }

    public function __toString(): string
    {
        // 🐛 emoji v
        $title = '%F0%9F%90%9B'.urlencode(" [{$this->instanceType}] Generated Issue: {$this->getClassName()}");

        $currentBehavior = urlencode(
            "The API has returned an error: \n```{$this->name}```\nStatus code: ```{$this->code}```\nMessage: ```{$this->error}```\nTrace: ```{$this->trace}```"
        );

        $expectedBehavior = urlencode('The API should have returned a successful response with data.');
        $env = urlencode(
            "Jikan Parser Version**: ```{$this->jikanVersion}```\nPHP: ```{$this->phpVersion}```\nIs redis used: ```{$this->redisRunning}```"
        );
        $reproSteps = urlencode(
            "Http Request: `{$this->requestMethod} {$this->requestUri}"
        );

        return "https://github.com/{$this->repo}/issues/new?template=bug.yml&title={$title}&system_env={$env}&repro_steps={$reproSteps}&expected_behavior={$expectedBehavior}&current_behavior={$currentBehavior}";
    }

    public function getClassName(): string
    {
        $path = explode('\\', $this->name);

        return array_pop($path);
    }

    public function setName(string $name): GithubReport
    {
        $this->name = $name;

        return $this;
    }

    public function setCode(string $code): GithubReport
    {
        $this->code = $code;

        return $this;
    }

    public function setError(string $error): GithubReport
    {
        $this->error = $error;

        return $this;
    }

    public function setRepo(string $repo): GithubReport
    {
        $this->repo = $repo;

        return $this;
    }

    public function setTrace(string $trace): GithubReport
    {
        $this->trace = $trace;

        return $this;
    }

    public function setJikanVersion(string $jikanVersion): GithubReport
    {
        $this->jikanVersion = $jikanVersion;

        return $this;
    }

    public function setRedisRunning(string $redisRunning): GithubReport
    {
        $this->redisRunning = $redisRunning;

        return $this;
    }

    public function setInstanceType(string $instanceType): GithubReport
    {
        $this->instanceType = $instanceType;

        return $this;
    }
}
