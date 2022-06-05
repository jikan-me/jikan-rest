<?php

namespace App;

trait IsoDateFormatter
{
    protected function formatIsoDateTime(string $d): string
    {
        $dt = explode('-', $d);
        return (new \DateTime())
            ->setDate(
                $start_date[0] ?? date('Y'),
                $start_date[1] ?? 1,
                $start_date[2] ?? 1
            )
            ->format(\DateTimeInterface::ISO8601);
    }
}
