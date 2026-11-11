<?php

namespace App\Events;

/**
 * Class SourceHeartbeatEvent
 */
class SourceHeartbeatEvent extends Event
{
    public const BAD_HEALTH = 1;

    public const GOOD_HEALTH = 0;

    public int $health;

    public int $status;

    /**
     * SourceHeartbeatEvent constructor.
     */
    public function __construct(?int $health, ?int $status)
    {
        $this->health = $health ?? self::BAD_HEALTH;
        $this->status = $status ?? 0;
    }
}
