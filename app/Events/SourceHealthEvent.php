<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Class SourceHealthEvent
 * @package App\Events
 */
class SourceHealthEvent extends Event
{
    public const BAD_HEALTH = 1;
    public const GOOD_HEALTH = 0;

    public $health;
    public $status;

    /**
     * SourceHealthEvent constructor.
     * @param int $health
     * @param int $status
     */
    public function __construct(int $health = self::BAD_HEALTH, ?int $status)
    {
        $this->health = $health;
        $this->status = $status ?? 0;
    }
}
