<?php

namespace App\Providers;

use Jikan\Model\Common\DateRange;
use Jikan\Model\Common\MalUrl;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;

class SerializerFactory
{
    public static function createV4(): Serializer
    {
        $serializer = (new SerializerBuilder())
            ->addMetadataDir(__DIR__.'/../../storage/app/metadata.v4')
            ->configureHandlers(
                function (HandlerRegistry $registry) {
                    $registry->registerHandler(
                        GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                        MalUrl::class,
                        'json',
                        \Closure::fromCallable('self::convertMalUrl')
                    );

                    $registry->registerHandler(
                        GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                        DateRange::class,
                        'json',
                        \Closure::fromCallable('self::convertDateRange')
                    );

                    $registry->registerHandler(
                        GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                        \DateTimeImmutable::class,
                        'json',
                        \Closure::fromCallable('self::convertDateTimeImmutable')
                    );
                }
            )
            ->setSerializationContextFactory(new SerializationContextFactory())
            ->build();

        return $serializer;
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

    private static function convertDateTimeImmutable($visitor, \DateTimeImmutable $obj, array $type): ?string
    {
        return $obj ? $obj->format(DATE_ATOM) : null;
    }
}
