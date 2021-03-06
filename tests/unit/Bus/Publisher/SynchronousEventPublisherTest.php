<?php

namespace Aztech\Events\Tests\Bus\Publisher;

use Aztech\Events\Bus\Publishers\SynchronousEventPublisher;
use Aztech\Events\Bus\Publisher\SynchronousPublisher;
use Aztech\Events\Bus\Event;
use Aztech\Events\Bus\Dispatcher;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;

class SynchronousEventPublisherTest extends \PHPUnit_Framework_TestCase
{

    private $mockDispatcher;

    protected function setUp()
    {
        $this->mockDispatcher = $this->getMock('\Aztech\Events\Dispatcher');
    }

    public function testPublishForwardsEventToDispatcherSynchronously()
    {
        $event = $this->getMock('\Aztech\Events\Event');

        $this->mockDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->equalTo($event));

        $publisher = new SynchronousPublisher($this->mockDispatcher);

        $publisher->publish($event);
    }

    public function testProcessNextDoesNotBlock()
    {
        $publisher = new SynchronousPublisher($this->mockDispatcher);

        $publisher->processNext($this->mockDispatcher);
    }

    public function testBoundSubscribersAreInvoked()
    {
        $event = new Event('category');
        $subscriber = $this->getMock('\Aztech\Events\Subscriber');

        $subscriber->expects($this->any())
            ->method('supports')
            ->with($event)
            ->willReturn(true);

        $subscriber->expects($this->once())
            ->method('handle')
            ->with($event);

        $publisher = new SynchronousPublisher();
        $publisher->on('#', $subscriber);

        $publisher->publish($event);
    }

    public function testBoundSubscribersAreNotInvokedWhenTheyDontSupportAnEvent()
    {
        $event = new Event('category');
        $subscriber = $this->getMock('\Aztech\Events\Subscriber');

        $subscriber->expects($this->any())
            ->method('supports')
            ->with($event)
            ->willReturn(false);

        $subscriber->expects($this->never())
            ->method('handle');

        $publisher = new SynchronousPublisher();
        $publisher->on('#', $subscriber);

        $publisher->publish($event);
    }


    public function testBoundSubscribersAreNotInvokedWhenCategoryDoesNotMatchFilter()
    {
        $event = new Event('category');
        $subscriber = $this->getMock('\Aztech\Events\Subscriber');

        $subscriber->expects($this->any())
        ->method('supports')
            ->with($event)
            ->willReturn(true);

        $subscriber->expects($this->never())
            ->method('handle');

        $publisher = new SynchronousPublisher();

        $publisher->on('test.#', $subscriber);

        $publisher->publish($event);
    }

    public function testSubscriberExceptionsDoNotBubbleAndSubsequenceSubcribersAreInvoked()
    {
        $event = new Event('category');
        $subscriber = $this->getMock('\Aztech\Events\Subscriber');

        $subscriber->expects($this->any())
            ->method('supports')
            ->with($event)
            ->willReturn(true);

        $subscriber->expects($this->at(1))
            ->method('handle')
            ->willThrowException(new \Exception());

        $subscriber->expects($this->at(3))
            ->method('handle')
            ->with($event);

        $publisher = new SynchronousPublisher();

        $publisher->on('#', $subscriber);
        $publisher->on('#', $subscriber);

        $publisher->publish($event);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSubscribingWithNonCallableAndNonSubscriberArgThrowsException()
    {
        $publisher = new SynchronousPublisher();

        $publisher->on('*', array());
    }

    public function testCallbacksAreCorrectlyHandled()
    {
        $publisher = new SynchronousPublisher();
        $invoked = false;

        $callback = function() use (& $invoked) {
            $invoked = true;
        };

        $publisher->on('*', $callback);

        $publisher->publish(new Event('test'));

        $this->assertTrue($invoked);
    }
}
