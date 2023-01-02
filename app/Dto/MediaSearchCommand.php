<?php

namespace App\Dto;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\Validation\AfterOrEqual;
use Spatie\LaravelData\Attributes\Validation\BeforeOrEqual;
use Spatie\LaravelData\Attributes\Validation\Between;
use Spatie\LaravelData\Attributes\Validation\DateFormat;
use Spatie\LaravelData\Attributes\Validation\GreaterThanOrEqualTo;
use Spatie\LaravelData\Attributes\Validation\LessThanOrEqualTo;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Prohibits;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

class MediaSearchCommand extends SearchCommand
{
    #[MapInputName("min_score"), MapOutputName("min_score"), Between(1.00, 9.99), Numeric]
    public float|Optional $minScore;

    #[MapInputName("max_score"), MapOutputName("max_score"), Between(1.00, 9.99), Numeric]
    public float|Optional $maxScore;

    #[Between(1.00, 9.99), Numeric, Prohibits(["min_score", "max_score"])]
    public float|Optional $score;

    public bool|Optional $sfw;

    public string|Optional $genres;

    #[MapInputName("genres_exclude"), MapOutputName("genres_exclude")]
    public string|Optional $genresExclude;

    #[WithCast(DateTimeInterfaceCast::class), WithTransformer(DateTimeInterfaceTransformer::class)]
    #[BeforeOrEqual("end_date"), DateFormat("Y-m-d")]
    public CarbonImmutable|Optional $start_date;

    #[WithCast(DateTimeInterfaceCast::class), WithTransformer(DateTimeInterfaceTransformer::class)]
    #[AfterOrEqual("start_date"), DateFormat("Y-m-d")]
    public CarbonImmutable|Optional $end_date;
}
