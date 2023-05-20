<?php

namespace App\Jobs;

use Carbon\Carbon;
use DateTime;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Queue\Middleware\ThrottlesExceptions;
use MongoDB\BSON\UTCDateTime;

/**
 *
 */
class UpdateResource extends Job implements ShouldBeUnique
{
    /**
     * @var string
     */
    public string $resource;

    /**
     * @var int
     */
    public int $malId;

    /**
     * @var string
     */
    public string $fingerprint;

    /**
     * @var int
     */
    public int $uniqueFor = 3600;

    /**
     * @var int
     */
    public int $maxExceptions = 5;

    /**
     * @var int
     */
    public int $timeout = 30;

    /**
     * @var int
     */
    public int $tries = 3;

    /**
     * @return int
     */
    public function uniqueId()
    {
        return $this->malId;
    }

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        string $resource,
        int $malId,
        string $fingerprint
    )
    {
        $this->resource = $resource;
        $this->malId = $malId;
        $this->fingerprint = $fingerprint;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $instance = new $this->resource;

        // @todo handle exceptions
        $response = $instance::scrape($this->malId);

        $response = ['updated_at' => new UTCDateTime()]
            + $response;

        $instance::query()
            ->where('mal_id', $this->malId)
            ->update($response);
    }

    /**
     * @return array
     */
    public function middleware() : array
    {
        return [
            (new ThrottlesExceptions(5, 10))
                ->backoff(60)
                ->by("$this->resource:$this->malId")
        ];
    }

    /**
     * @return DateTime
     */
    public function retryUntil() : DateTime
    {
        return Carbon::now()
            ->addMinutes(10);
    }
}
