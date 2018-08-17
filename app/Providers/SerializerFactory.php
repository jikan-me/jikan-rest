<?php

namespace App\Providers;

use Jikan\Model\Common\DateRange;
use Jikan\Model\Common\MalUrl;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;

class SerializerFactory
{

    public static function createV2(): Serializer
    {
        return (new SerializerBuilder())
            ->addMetadataDir(__DIR__.'/../../storage/app/metadata.v2')
            ->configureHandlers(
                function (HandlerRegistry $registry) {
                    $registry->registerHandler(
                        'serialization',
                        MalUrl::class,
                        'json',
                        function ($visitor, MalUrl $obj, array $type) {
                            return [
                                'mal_id' => $obj->getMalId(),
                                'type'   => $obj->getType(),
                                'name'   => $obj->getTitle(),
                                'url'    => $obj->getUrl(),
                            ];
                        }
                    );

                    $registry->registerHandler(
                        'serialization',
                        DateRange::class,
                        'json',
                        function ($visitor, DateRange $obj, array $type) {
                            return [
                                'from' => $obj->getFrom() ? $obj->getFrom()->format(DATE_ATOM) : null,
                                'to'   => $obj->getUntil() ? $obj->getUntil()->format(DATE_ATOM) : null,
                            ];
                        }
                    );

                    $registry->registerHandler(
                        'serialization',
                        \DateTimeImmutable::class,
                        'json',
                        function ($visitor, \DateTimeImmutable $obj, array $type) {
                            return $obj ? $obj->format(DATE_ATOM) : null;
                        }
                    );
                }
            )
            ->build();
    }

    public static function createV3(): Serializer
    {
        $serializer = (new SerializerBuilder())
            ->addMetadataDir(__DIR__.'/../../storage/app/metadata.v3')
            ->configureHandlers(
                function (HandlerRegistry $registry) {
                    $registry->registerHandler(
                        'serialization',
                        MalUrl::class,
                        'json',
                        function ($visitor, MalUrl $obj, array $type) {
                            return [
                                'mal_id' => $obj->getMalId(),
                                'type'   => $obj->getType(),
                                'name'   => $obj->getTitle(),
                                'url'    => $obj->getUrl(),
                            ];
                        }
                    );

                    $registry->registerHandler(
                        'serialization',
                        DateRange::class,
                        'json',
                        function ($visitor, DateRange $obj, array $type) {
                            return [
                                'from'   => $obj->getFrom() ? $obj->getFrom()->format(DATE_ATOM) : null,
                                'to'     => $obj->getUntil() ? $obj->getUntil()->format(DATE_ATOM) : null,
                                'string' => (string)$obj,
                            ];
                        }
                    );

                    $registry->registerHandler(
                        'serialization',
                        \DateTimeImmutable::class,
                        'json',
                        function ($visitor, \DateTimeImmutable $obj, array $type) {
                            return $obj ? $obj->format(DATE_ATOM) : null;
                        }
                    );
                }
            )
            ->build();
        $serializer->setSerializationContextFactory(new SerializationContextFactory());

        return $serializer;
    }
}