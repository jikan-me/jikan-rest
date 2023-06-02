<?php

namespace App\Providers;

use App\CarbonDateRange;
use Jikan\Model\Common\DateRange;
use Jikan\Model\Common\MalUrl;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use MongoDB\BSON\UTCDateTime;

class SerializerFactory
{
    public static function createV4(): Serializer
    {
        return (new SerializerBuilder())
            ->addMetadataDir(__DIR__.'/../../storage/app/metadata.v4')
            ->configureHandlers(
                function (HandlerRegistry $registry) {
                    $registry->registerHandler(
                        GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                        MalUrl::class,
                        'json',
                        self::convertMalUrl(...)
                    );

                    $registry->registerHandler(
                        GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                        DateRange::class,
                        'json',
                        self::convertDateRange(...)
                    );

                    $registry->registerHandler(
                        GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                        \DateTimeImmutable::class,
                        'json',
                        self::convertDateTimeImmutable(...)
                    );

                    $registry->registerHandler(
                        GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                        CarbonDateRange::class,
                        'json',
                        self::convertCarbonDateRange(...)
                    );

                    $registry->registerHandler(
                        GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                        UTCDateTime::class,
                        'json',
                        self::convertBsonDateTime(...)
                    );
                }
            )
            ->setSerializationContextFactory(new SerializationContextFactory())
            ->build();
    }

    private static function convertMalUrl($visitor, MalUrl $obj, array $type): array
    {
        return [
            'mal_id' => $obj->getMalId(),
            'type'   => $obj->getType(),
            'name'   => $obj->getTitle(),
            'url'    => $obj->getUrl(),
        ];
    }

    private static function convertMalUrlv2($visitor, MalUrl $obj, array $type): array
    {
        return [
            'mal_id' => $obj->getMalId(),
            'type'   => $obj->getType(),
            'title'   => $obj->getTitle(),
            'name'   => $obj->getTitle(),
            'url'    => $obj->getUrl(),
        ];
    }

    private static function convertDateRange($visitor, DateRange $obj, array $type): array
    {
        return [
            // todo: update the storage method of dates from string to UTCDateTime BSON object.
            // 'from'   => $obj->getFrom() ? new UTCDateTime($obj->getFrom()->getTimestamp()) : null,
            // 'to'     => $obj->getUntil() ? new UTCDateTime($obj->getUntil()->getTimestamp()) : null,
            'from'   => $obj->getFrom() ? $obj->getFrom()->format(DATE_ATOM) : null,
            'to'     => $obj->getUntil() ? $obj->getUntil()->format(DATE_ATOM) : null,
            'prop'   => [
                'from' => [
                    'day' => $obj->getFromProp()->getDay(),
                    'month' => $obj->getFromProp()->getMonth(),
                    'year' => $obj->getFromProp()->getYear()
                ],
                'to' => [
                    'day' => $obj->getUntilProp()->getDay(),
                    'month' => $obj->getUntilProp()->getMonth(),
                    'year' => $obj->getUntilProp()->getYear()
                ],
            ],
            'string' => (string)$obj,
        ];
    }

    private static function convertCarbonDateRange($visitor, CarbonDateRange $obj, array $type): array
    {
        $from = $obj->getFrom();
        $to = $obj->getUntil();

        return [
            // todo: update the storage method of dates from string to UTCDateTime BSON object.
            // 'from'   => $from !== null ? new UTCDateTime($from->getTimestamp()) : null,
            // 'to'     => $to !== null ? new UTCDateTime($to->getTimestamp()) : null,
            'from' => $from?->toAtomString(),
            'to' => $to?->toAtomString(),
            'prop'   => [
                'from' => [
                    'day' => $obj->getFromProp()->getDay(),
                    'month' => $obj->getFromProp()->getMonth(),
                    'year' => $obj->getFromProp()->getYear()
                ],
                'to' => [
                    'day' => $obj->getUntilProp()->getDay(),
                    'month' => $obj->getUntilProp()->getMonth(),
                    'year' => $obj->getUntilProp()->getYear()
                ],
            ],
            'string' => (string)$obj,
        ];
    }

    private static function convertDateTimeImmutable($visitor, \DateTimeImmutable $obj, array $type): ?string
    {
        return $obj ? $obj->format(DATE_ATOM) : null;
    }

    private static function convertBsonDateTime($visitor, UTCDateTime $obj, array $type, SerializationContext $context): string
    {
        return $obj->toDateTime()->format(DATE_ATOM);
    }
}
