<?php

namespace App\Providers;

use JMS\Serializer\ContextFactory\SerializationContextFactoryInterface;
use JMS\Serializer\SerializationContext;

/**
 * Default Serialization Context Factory.
 */
class SerializationContextFactory implements SerializationContextFactoryInterface
{
    /**
     * {@InheritDoc}
     */
    public function createSerializationContext()
    {
        return (new SerializationContext())
            ->setSerializeNull(true);
    }
}
