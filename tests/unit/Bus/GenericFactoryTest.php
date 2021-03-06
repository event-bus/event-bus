<?php

namespace Aztech\Events\Tests\Bus;

use Aztech\Events\Bus\GenericFactory;
use Aztech\Events\Bus\Serializer\NativeSerializer;
use Aztech\Events\Bus\Channel\NullChannelProvider;

class GenericFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testCreateProcessorReturnsProcessor()
    {
        $factory = new GenericFactory(new NativeSerializer(), new NullChannelProvider());

        $this->assertInstanceOf('\Aztech\Events\Bus\Processor', $factory->createProcessor());
    }

    public function testCreatePublisherReturnsPublisher()
    {
        $factory = new GenericFactory(new NativeSerializer(), new NullChannelProvider());

        $this->assertInstanceOf('\Aztech\Events\Bus\Publisher', $factory->createPublisher());
    }
}
