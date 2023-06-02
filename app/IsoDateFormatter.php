<?php

namespace App;

trait IsoDateFormatter
{
    protected function formatIsoDateTime(string $d): string
    {
        $dt = explode('-', $d);
        return (new \DateTime())
            ->setDate(
                $dt[0] ?? date('Y'),
                $dt[1] ?? 1,
                $dt[2] ?? 1
            )
            ->setTime(0, 0)
            ->format(DATE_ATOM);
    }
}
