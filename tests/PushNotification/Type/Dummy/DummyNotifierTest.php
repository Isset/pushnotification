<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Tests\Type\Dummy;

use IssetBV\PushNotification\Core\Connection\ConnectionHandlerImpl;
use IssetBV\PushNotification\Core\NotifierAbstract;
use IssetBV\PushNotification\Tests\Core\NotifierAbstractTest;
use PHPUnit\Framework\TestCase;

final class DummyNotifierTest extends NotifierAbstractTest
{
    /** @test */
    public function it_should_send_a_message_successfully()
    {
        $connectionHandler = new ConnectionHandlerImpl();
        $connectionHandler->addConnection(DummyConnection::withDefault());

        $sut = new DummyNotifier($connectionHandler);

        $sut->send(DummyMessage::simple());

        TestCase::assertTrue($sut->sendMessageWasCalled());
    }

    /** @test */
    public function it_should_send_a_message_unsuccessfully()
    {
        //irrelevant
        TestCase::assertTrue(true);
    }

    /** @test */
    public function it_should_queue_a_message()
    {
        $connectionHandler = new ConnectionHandlerImpl();
        $connectionHandler->addConnection(DummyConnection::withDefault());

        $sut = new DummyNotifier($connectionHandler);

        $sut->queue(DummyMessage::simple());

        TestCase::assertTrue($sut->queueMessageWasCalled());
    }

    /** @test */
    public function it_should_not_flush_the_queue_if_the_queue_is_empty()
    {
        $connectionHandler = new ConnectionHandlerImpl();
        $connectionHandler->addConnection(DummyConnection::withDefault());

        $sut = new DummyNotifier($connectionHandler);

        $sut->flushQueue();

        TestCase::assertEquals(0, $sut->flushQueueItemWasCalledTimes());
    }

    /** @test */
    public function it_should_only_flush_queues_from_a_specific_connection()
    {
        $connectionHandler = new ConnectionHandlerImpl();
        $connectionHandler->addConnection(DummyConnection::withDefault());

        $sut = new DummyNotifier($connectionHandler);
        $sut->queue(DummyMessage::simple(), 'queue1');
        $sut->flushQueue('queue2');

        TestCase::assertSame(0, $sut->flushQueueItemWasCalledTimes());

        $sut->flushQueue('queue1');

        TestCase::assertSame(1, $sut->flushQueueItemWasCalledTimes());
    }

    /** @test */
    public function it_should_flush_all_queues_if_no_connection_was_specified()
    {
        $connectionHandler = new ConnectionHandlerImpl();
        $connectionHandler->addConnection(DummyConnection::withDefault());

        $sut = new DummyNotifier($connectionHandler);
        $sut->queue(DummyMessage::simple(), 'queue1');
        $sut->queue(DummyMessage::simple(), 'queue2');
        $sut->flushQueue();

        TestCase::assertSame(2, $sut->flushQueueItemWasCalledTimes());
    }

    protected function getNotifierUnderTest($connectionHandler = null): NotifierAbstract
    {
        $connectionHandler = $connectionHandler ?? new ConnectionHandlerImpl();

        return new DummyNotifier($connectionHandler);
    }

    protected function getNotifierUnderTestConnection()
    {
        return DummyConnection::class;
    }

    protected function getNotifierUnderTestMessageEnvelope()
    {
        return DummyMessageEnvelope::class;
    }

    protected function getNotifierUnderTestMessage()
    {
        return new DummyMessage('test', 'message');
    }
}
