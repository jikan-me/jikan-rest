<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use Predis\Connection\ConnectionException;

/**
 * Class GithubReport
 * @package App\Exceptions
 */
class GithubReport
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $code;
    /**
     * @var string
     */
    private $error;

    /**
     * @var string
     */
    private $requestUri;
    /**
     * @var string
     */
    private $requestMethod;

    /**
     * @var string
     */
    private $repo;

    /**
     * @param \Exception $exception
     * @param Request $request
     * @return string
     */
    public static function make(\Exception $exception, Request $request, ?string $repo = null) : self
    {
        $report = new self;
        $report->name = \get_class($exception);
        $report->code = $exception->getCode();
        $report->error = $exception->getMessage();
        $report->repo = $repo ?? env('GITHUB_REST', 'jikan-me/jikan-rest');
        $report->requestUri = $request->getRequestUri();
        $report->requestMethod = $request->getMethod();

        return $report;
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        $title = urlencode("Generated Issue: {$this->getClassName()}");
        $body = urlencode(
            "**Exception:** `{$this->name}`\n**Code:** `{$this->code}`\n**Message:** `{$this->error}`\n**Request:** `{$this->requestMethod} {$this->requestUri}`\n\n**Enter Addition Details:**\n"
        );

        return "https://github.com/{$this->repo}/issues/new?title={$title}&body={$body}";
    }

    /**
     * @return string
     */
    public function getClassName() : string
    {
        $path = explode('\\', $this->name);
        return array_pop($path);
    }

    /**
     * @param string $name
     */
    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code) : void
    {
        $this->code = $code;
    }

    /**
     * @param string $error
     */
    public function setError(string $error) : void
    {
        $this->error = $error;
    }

    /**
     * @param string $repo
     */
    public function setRepo(string $repo) : void
    {
        $this->repo = $repo;
    }
}