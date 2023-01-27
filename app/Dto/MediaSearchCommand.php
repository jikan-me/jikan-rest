<?php

namespace App\Dto;

use Carbon\CarbonImmutable;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\Validation\AfterOrEqual;
use Spatie\LaravelData\Attributes\Validation\BeforeOrEqual;
use Spatie\LaravelData\Attributes\Validation\Between;
use Spatie\LaravelData\Attributes\Validation\DateFormat;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Prohibits;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

class MediaSearchCommand extends SearchCommand
{
    #[MapInputName("min_score"), MapOutputName("min_score"), Between(0.00, 10.00), Numeric]
    public float|Optional $minScore;

    #[MapInputName("max_score"), MapOutputName("max_score"), Between(1.00, 10.00), Numeric]
    public float|Optional $maxScore;

    #[Between(1.00, 9.99), Numeric, Prohibits(["min_score", "max_score"])]
    public float|Optional $score;

    public bool|Optional $sfw;

    public string|Optional $genres;

    #[MapInputName("genres_exclude"), MapOutputName("genres_exclude")]
    public string|Optional $genresExclude;

    #[
        BeforeOrEqual("end_date"),
        DateFormat("Y-m-d"),
        Sometimes,
        Required,
        WithCast(DateTimeInterfaceCast::class),
        WithTransformer(DateTimeInterfaceTransformer::class)
    ]
    public CarbonImmutable|Optional $start_date;

    #[
        AfterOrEqual("start_date"),
        DateFormat("Y-m-d"),
        Sometimes,
        Required,
        WithCast(DateTimeInterfaceCast::class),
        WithTransformer(DateTimeInterfaceTransformer::class)
    ]
    public CarbonImmutable|Optional $end_date;

    public static function withValidator(Validator $validator): void
    {
        $validator->sometimes("min_score", "lte:max_score", fn ($input) => !empty($input->max_score));
        $validator->sometimes("max_score", "gte:min_score", fn ($input) => !empty($input->min_score));
    }
}
