<?php

namespace IssetBV\PushNotification\Tests\Type\Apple;

use IssetBV\PushNotification\Core\Connection\ConnectionHandlerImpl;
use IssetBV\PushNotification\Core\Connection\ConnectionResponseImpl;
use IssetBV\PushNotification\Core\NotifierAbstract;
use IssetBV\PushNotification\Tests\Core\NotifierAbstractTest;
use IssetBV\PushNotification\Tests\Type\Dummy\DummyMessage;
use IssetBV\PushNotification\Type\Apple\AppleConnection;
use IssetBV\PushNotification\Type\Apple\AppleNotifier;
use IssetBV\PushNotification\Type\Apple\Exception\AppleNotifyFailedException;
use IssetBV\PushNotification\Type\Apple\Message\AppleMessageAps;
use IssetBV\PushNotification\Type\Apple\Message\AppleMessageEnvelope;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class AppleNotifierTest extends NotifierAbstractTest
{
    /** @test */
    public function it_should_only_handle_apple_messages()
    {
        $dummyMessage = DummyMessage::simple();
        $message = new AppleMessageAps('test');

        TestCase::assertFalse($this->notifier->handles($dummyMessage));
        TestCase::assertTrue($this->notifier->handles($message));
    }

    /** @test */
    public function it_should_throw_an_exception_if_sending_goes_wrong()
    {
        $this->expectException(AppleNotifyFailedException::class);
        $this->expectExceptionMessage('an error occurred');

        $logger = $this->prophesize(LoggerInterface::class);

        $message = $this->getNotifierUnderTestMessage();

        $connection = $this->prophesize($this->getNotifierUnderTestConnection());
        $connection->isDefault()->willReturn(true);
        $connection->setLogger(Argument::any())->willReturn();
        $connection->getType()->willReturn('apple-connection');
        $connection->sendAndReceive($message)->willThrow(new \Exception('an error occurred'));

        $connectionHandler = new ConnectionHandlerImpl();
        $connectionHandler->addConnection($connection->reveal());

        $sut = $this->getNotifierUnderTest($connectionHandler);
        $sut->setLogger($logger->reveal());
        $sut->send($message);

        $this->logger->error(Argument::any())->shouldHaveBeenCalled();
    }

    /**
     * @dataProvider errorResponse
     * @test
     *
     * @param mixed $errorResponse
     */
    public function it_should_mark_all_messages_as_failed_if_no_identifier_exists_in_the_envelop($errorResponse)
    {
        $this->expectException(AppleNotifyFailedException::class);
        $this->expectExceptionMessage('Message gave an error but no response all messages marked as failed');

        $message = $this->getNotifierUnderTestMessage();

        $response = new ConnectionResponseImpl();
        $response->setSuccess(false);
        $response->setResponse($errorResponse);

        $connection = $this->prophesize($this->getNotifierUnderTestConnection());
        $connection->isDefault()->willReturn(true);
        $connection->setLogger(Argument::any())->willReturn();
        $connection->getType()->willReturn('apple-connection');
        $connection->send($message)->willReturn();
        $connection->getResponseData()->willReturn($response);

        $connectionHandler = new ConnectionHandlerImpl();
        $connectionHandler->addConnection($connection->reveal());

        $sut = $this->getNotifierUnderTest($connectionHandler);
        //$sut->setLogger($logger->reveal());
        $sut->queue($message);
        $sut->flushQueue();
    }

    /** @test */
    public function it_should_throw_an_exception_if_an_identifier_doesnt_exists_in_the_queue()
    {
        $this->expectException(AppleNotifyFailedException::class);
        $this->expectExceptionMessage('Failed identifier not found: non-existing-identifier');

        $message = $this->getNotifierUnderTestMessage();

        TestCase::assertInternalType('int', $message->getIdentifier());

        $response = new ConnectionResponseImpl();
        $response->setSuccess(false);
        $response->setResponse(['identifier' => 'non-existing-identifier']);

        $connection = $this->prophesize($this->getNotifierUnderTestConnection());
        $connection->isDefault()->willReturn(true);
        $connection->setLogger(Argument::any())->willReturn();
        $connection->getType()->willReturn('apple-connection');
        $connection->send($message)->willReturn();
        $connection->getResponseData()->willReturn($response);

        $connectionHandler = new ConnectionHandlerImpl();
        $connectionHandler->addConnection($connection->reveal());

        $sut = $this->getNotifierUnderTest($connectionHandler);
        $sut->queue($message);
        $sut->flushQueue();
    }

    /** @test */
    public function it_should_mark_a_failed_message_from_the_queue_as_failed_while_marking_succeeded_as_success()
    {
        $message1 = new AppleMessageAps('message1');
        $message2 = new AppleMessageAps('message2');
        $message3 = new AppleMessageAps('message3');

        $responseFailed = new ConnectionResponseImpl();
        $responseFailed->setSuccess(false);
        $responseFailed->setResponse(['identifier' => $message2->getIdentifier()]);

        $responseSuccess = new ConnectionResponseImpl();
        $responseSuccess->setSuccess(true);
        /**
         $response3->setResponse(['identifier' => $message3->getIdentifier()]);
         **/
        $connection = $this->prophesize($this->getNotifierUnderTestConnection());
        $connection->isDefault()->willReturn(true);
        $connection->setLogger(Argument::any())->willReturn();
        $connection->getType()->willReturn('apple-connection');
        $connection->send(Argument::any())->willReturn();
        $connection->getResponseData()->willReturn($responseFailed, $responseSuccess);

        $connectionHandler = new ConnectionHandlerImpl();
        $connectionHandler->addConnection($connection->reveal());

        $sut = $this->getNotifierUnderTest($connectionHandler);
        $messageEnvelope1 = $sut->queue($message1);
        $messageEnvelope2 = $sut->queue($message2);
        $messageEnvelope3 = $sut->queue($message3);
        $sut->flushQueue();

        TestCase::assertSame('success', $messageEnvelope1->getState());
        TestCase::assertSame('failed', $messageEnvelope2->getState());
        TestCase::assertSame('success', $messageEnvelope3->getState());
    }

    /**
     * DataProvider.
     *
     * @return array
     */
    public function errorResponse()
    {
        return [
            [null],
            [[]],
        ];
    }

    protected function getNotifierUnderTest($connectionHandler = null): NotifierAbstract
    {
        $connectionHandler = $connectionHandler ?? new ConnectionHandlerImpl();

        return new AppleNotifier($connectionHandler);
    }

    protected function getNotifierUnderTestConnection()
    {
        return AppleConnection::class;
    }

    protected function getNotifierUnderTestMessageEnvelope()
    {
        return AppleMessageEnvelope::class;
    }

    protected function getNotifierUnderTestMessage()
    {
        return new AppleMessageAps('test');
    }
}
