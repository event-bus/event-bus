<?php

namespace Aztech\Events\Bus\Serializer;

use Aztech\Events\Bus\Serializer;
use Aztech\Events\Event;

class NativeSerializer implements Serializer
{

    public function serialize(Event $object)
    {
        return serialize($object);
    }

    public function deserialize($object)
    {
        return unserialize($object);
    }
}
