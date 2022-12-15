<?php

namespace App;

use Illuminate\Support\Carbon;
use Jikan\Model\Common\DateProp;

/**
 * Class representing a date range via Carbon objects.
 * 
 * Mainly used for testing.
 */
class CarbonDateRange
{
    private ?Carbon $fromObj;
    private ?Carbon $untilObj;

    public function __construct(?Carbon $from, ?Carbon $to)
    {
        $this->fromObj = $from;
        $this->untilObj = $to;
    }

    public function __toString()
    {
        $result = "";
        if ($this->untilObj === null && $this->fromObj !== null && $this->fromObj->day == 1 && $this->fromObj->month == 1) {
            $result = "{$this->fromObj->year}";
        }
        else if ($this->untilObj === null && $this->fromObj !== null && $this->fromObj->day == 1) {
            $result = $this->fromObj->format("M, Y");
        }
        else if ($this->untilObj === null && $this->fromObj !== null) {
            $result = $this->fromObj->format("M d, Y");
        }
        else if ($this->untilObj !== null && $this->fromObj !== null) {
            $result = "{$this->fromObj->format("M d, Y")} to {$this->untilObj->format("M d, Y")}";
        }
        return $result;
    }

    public function getFrom(): ?Carbon
    {
        return $this->fromObj;
    }

    public function getUntil(): ?Carbon
    {
        return $this->untilObj;
    }

    public function getFromProp(): ?DateProp
    {
        return DateProp::fromDateTime($this->fromObj?->toDateTimeImmutable());
    }

    public function getUntilProp(): ?DateProp
    {
        return DateProp::fromDateTime($this->untilObj?->toDateTimeImmutable());
    }
}
