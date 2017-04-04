<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Tests\Core;

use IssetBV\PushNotification\Core\Connection\Connection;
use IssetBV\PushNotification\Core\Connection\ConnectionHandlerImpl;
use IssetBV\PushNotification\Core\Connection\ConnectionResponseImpl;
use IssetBV\PushNotification\Core\Message\Message;
use IssetBV\PushNotification\Core\Message\MessageEnvelope;
use IssetBV\PushNotification\Core\NotifierAbstract;
use IssetBV\PushNotification\Tests\Type\Dummy\DummyLogger;
use IssetBV\PushNotification\Type\Apple\AppleConnection;
use PhpOption\Option;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\NullLogger;

abstract class NotifierAbstractTest extends TestCase
{
    /** @var NotifierAbstract */
    protected $notifier;

    public function setUp()
    {
        $this->notifier = $this->getNotifierUnderTest();
    }

    /** @test */
    public function it_should_retrieve_the_current_connection_handler()
    {
        TestCase::assertInstanceOf(ConnectionHandlerImpl::class, $this->notifier->getConnectionHandler());
    }

    /** @test */
    public function it_should_cascade_the_logger_to_the_connection_handler()
    {
        $connectionHandler = new ConnectionHandlerImpl();

        TestCase::assertInstanceOf(NullLogger::class, $connectionHandler->getLogger());

        $sut = $this->getNotifierUnderTest($connectionHandler);

        TestCase::assertInstanceOf(NullLogger::class, $sut->getLogger());

        $sut->setLogger(new DummyLogger());

        TestCase::assertInstanceOf(DummyLogger::class, $connectionHandler->getLogger());
        TestCase::assertInstanceOf(DummyLogger::class, $sut->getLogger());
    }

    /** @test */
    public function it_should_throw_an_exception_if_a_message_cant_be_handled_when_trying_to_send_it()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageRegExp('/Message of type ([^\s]+) couldn\'t be handled by this notifier/');

        /** @var ObjectProphecy|Message $message */
        $message = $this->prophesize(Message::class);

        $this->notifier->send($message->reveal());
    }

    /** @test */
    public function it_should_throw_an_exception_if_a_message_cant_be_handled_when_trying_to_queue_it()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageRegExp('/Message of type ([^\s]+) couldn\'t be handled by this notifier/');

        /** @var ObjectProphecy|Message $message */
        $message = $this->prophesize(Message::class);

        $this->notifier->queue($message->reveal());
    }

    /** @test */
    public function it_should_send_a_message_successfully()
    {
        $message = $this->getNotifierUnderTestMessage();
        $successFulResponse = $this->getSuccessfulResponse();

        $connection = $this->getStubConnection($message, 'connection', $successFulResponse);

        $connectionHandler = new ConnectionHandlerImpl();
        $connectionHandler->addConnection($connection->reveal());

        $sut = $this->getNotifierUnderTest($connectionHandler);
        $messageEnvelope = $sut->send($message);

        TestCase::assertInstanceOf($this->getNotifierUnderTestMessageEnvelope(), $messageEnvelope);
        TestCase::assertSame(MessageEnvelope::SUCCESS, $messageEnvelope->getState());
        TestCase::assertInstanceOf(Option::class, $messageEnvelope->getResponse());
        TestCase::assertSame(
            $successFulResponse,
            $messageEnvelope->getResponse()->get()
        );
    }

    /** @test */
    public function it_should_send_a_message_unsuccessfully()
    {
        $message = $this->getNotifierUnderTestMessage();

        $unSuccessfulResponse = $this->getUnSuccessfulResponse();

        $connection = $this->getStubConnection($message, 'connection', $unSuccessfulResponse);

        $connectionHandler = new ConnectionHandlerImpl();
        $connectionHandler->addConnection($connection->reveal());

        $sut = $this->getNotifierUnderTest($connectionHandler);
        $messageEnvelope = $sut->send($message);

        TestCase::assertInstanceOf($this->getNotifierUnderTestMessageEnvelope(), $messageEnvelope);
        TestCase::assertSame(MessageEnvelope::FAILED, $messageEnvelope->getState());
        TestCase::assertInstanceOf(Option::class, $messageEnvelope->getResponse());
        TestCase::assertSame(
            $unSuccessfulResponse,
            $messageEnvelope->getResponse()->get()
        );
    }

    /** @test */
    public function it_should_queue_a_message()
    {
        $message = $this->getNotifierUnderTestMessage();

        $connection = $this->getStubConnection($message, 'connection');

        $connectionHandler = new ConnectionHandlerImpl();
        $connectionHandler->addConnection($connection->reveal());

        $sut = $this->getNotifierUnderTest($connectionHandler);
        $messageEnvelope = $sut->queue($message);

        TestCase::assertInstanceOf($this->getNotifierUnderTestMessageEnvelope(), $messageEnvelope);
    }

    /** @test */
    public function it_should_not_flush_the_queue_if_the_queue_is_empty()
    {
        $message = $this->getNotifierUnderTestMessage();

        /** @var ObjectProphecy|Connection $connection */
        $connection = $this->getStubConnection($message, 'connection');

        $connectionHandler = new ConnectionHandlerImpl();
        $connectionHandler->addConnection($connection->reveal());

        $sut = $this->getNotifierUnderTest($connectionHandler);
        $sut->flushQueue();

        if ($this->getNotifierUnderTestConnection() === AppleConnection::class) {
            $connection->send($message)->shouldNotHaveBeenCalled();
        } else {
            $connection->sendAndReceive($message)->shouldNotHaveBeenCalled();
        }
    }

    /** @test */
    public function it_should_only_flush_queues_from_a_specific_connection()
    {
        $message = $this->getNotifierUnderTestMessage();

        /** @var ObjectProphecy|Connection $connection */
        $connection1 = $this->getStubConnection($message, 'connection1');
        $connection2 = $this->getStubConnection($message, 'connection2');

        $connectionHandler = new ConnectionHandlerImpl();
        $connectionHandler->addConnection($connection1->reveal());
        $connectionHandler->addConnection($connection2->reveal());

        $sut = $this->getNotifierUnderTest($connectionHandler);
        $sut->queue($message, 'connection1');
        $sut->queue($message, 'connection2');

        $result = $sut->flushQueue('connection2');

        TestCase::assertEmpty($result);

        if ($this->getNotifierUnderTestConnection() === AppleConnection::class) {
            $connection1->send($message)->shouldNotHaveBeenCalled();
            $connection2->send($message)->shouldHaveBeenCalled(1);
        } else {
            $connection1->sendAndReceive($message)->shouldNotHaveBeenCalled();
            $connection2->sendAndReceive($message)->shouldHaveBeenCalledTimes(1);
        }
    }

    /** @test */
    public function it_should_flush_all_queues_if_no_connection_was_specified()
    {
        $message = $this->getNotifierUnderTestMessage();

        $response1 = new ConnectionResponseImpl();
        $response1->setSuccess(true);
        $response1->setResponse('a response');

        $response2 = new ConnectionResponseImpl();
        $response2->setSuccess(true);
        $response2->setResponse('another response');

        /** @var ObjectProphecy|Connection $connection */
        $connection1 = $this->prophesize($this->getNotifierUnderTestConnection());
        $connection1->isDefault()->willReturn(true);
        $connection1->setLogger(Argument::any())->willReturn();
        $connection1->getType()->willReturn('connection1');
        if ($this->getNotifierUnderTestConnection() === AppleConnection::class) {
            $connection1->send($message)->willReturn();
            $connection1->getResponseData()->willReturn($response1);
        } else {
            $connection1->sendAndReceive($message)->willReturn($response1);
        }

        $connection2 = $this->prophesize($this->getNotifierUnderTestConnection());
        $connection2->isDefault()->willReturn(false);
        $connection2->setLogger(Argument::any())->willReturn();
        $connection2->getType()->willReturn('connection2');
        if ($this->getNotifierUnderTestConnection() === AppleConnection::class) {
            $connection2->send($message)->willReturn();
            $connection2->getResponseData()->willReturn($response2);
        } else {
            $connection2->sendAndReceive($message)->willReturn($response2);
        }

        $connectionHandler = new ConnectionHandlerImpl();
        $connectionHandler->addConnection($connection1->reveal());
        $connectionHandler->addConnection($connection2->reveal());

        $sut = $this->getNotifierUnderTest($connectionHandler);
        $sut->queue($message, 'connection1');
        $sut->queue($message, 'connection2');

        $result = $sut->flushQueue();

        TestCase::assertEmpty($result);

        if ($this->getNotifierUnderTestConnection() === AppleConnection::class) {
            $connection1->send($message)->shouldHaveBeenCalledTimes(1);
            $connection2->send($message)->shouldHaveBeenCalledTimes(1);
        } else {
            $connection1->sendAndReceive($message)->shouldHaveBeenCalledTimes(1);
            $connection2->sendAndReceive($message)->shouldHaveBeenCalledTimes(1);
        }
    }

    abstract protected function getNotifierUnderTest($connectionHandler = null): NotifierAbstract;

    abstract protected function getNotifierUnderTestMessage();

    abstract protected function getNotifierUnderTestConnection();

    abstract protected function getNotifierUnderTestMessageEnvelope();

    /**
     * @param $message
     * @param $response1
     * @param mixed $connectionName
     * @param null|mixed $response
     *
     * @return ObjectProphecy
     */
    private function getStubConnection($message, $connectionName, $response = null): ObjectProphecy
    {
        $response = $response ?? $this->getSuccessfulResponse();

        $connection = $this->prophesize($this->getNotifierUnderTestConnection());
        $connection->isDefault()->willReturn(true);
        $connection->setLogger(Argument::any())->willReturn();
        $connection->getType()->willReturn($connectionName);
        $connection->sendAndReceive($message)->willReturn($response);

        // we need this because apple send/receive uses sockets
        if ($this->getNotifierUnderTestConnection() === AppleConnection::class) {
            $connection->send($message)->willReturn();
            $connection->getResponseData()->willReturn($response);
        }

        return $connection;
    }

    /**
     * @return ConnectionResponseImpl
     */
    private function getSuccessfulResponse(): ConnectionResponseImpl
    {
        $response = new ConnectionResponseImpl();
        $response->setSuccess(true);
        $response->setResponse('a response');

        return $response;
    }

    /**
     * @return ConnectionResponseImpl
     */
    private function getUnSuccessfulResponse(): ConnectionResponseImpl
    {
        $response = new ConnectionResponseImpl();
        $response->setSuccess(false);
        $response->setResponse('a response');

        return $response;
    }
}
