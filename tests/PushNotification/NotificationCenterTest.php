<?php

namespace IssetBV\PushNotification\Tests;

use IssetBV\PushNotification\Core\Connection\ConnectionHandlerImpl;
use IssetBV\PushNotification\Core\Notifier;
use IssetBV\PushNotification\Exception\NotifierNotFoundException;
use IssetBV\PushNotification\NotificationCenter;
use IssetBV\PushNotification\Tests\Type\Dummy\DummyConnection;
use IssetBV\PushNotification\Tests\Type\Dummy\DummyLogger;
use IssetBV\PushNotification\Tests\Type\Dummy\DummyMessage;
use IssetBV\PushNotification\Tests\Type\Dummy\DummyNotifier;
use IssetBV\PushNotification\Type\Android\AndroidNotifier;
use IssetBV\PushNotification\Type\Android\Message\AndroidMessage;
use IssetBV\PushNotification\Type\Apple\AppleNotifier;
use IssetBV\PushNotification\Type\Apple\Message\AppleMessage;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class NotificationCenterTest extends TestCase
{
    /** @test */
    public function it_should_tell_us_if_one_of_the_notifiers_handles_the_message()
    {
        $message = new DummyMessage('we', 'dont care');

        $sut = new NotificationCenter();

        TestCase::assertFalse($sut->handles($message));

        $sut->addNotifier(new DummyNotifier(new ConnectionHandlerImpl()));

        TestCase::assertTrue($sut->handles($message));
    }

    /** @test */
    public function it_should_guard_against_adding_the_notification_center_to_itself()
    {
        $this->expectException(\LogicException::class);

        $notifier = new NotificationCenter();

        $sut = new NotificationCenter();

        $sut->addNotifier($notifier);
    }

    /** @test */
    public function it_should_cascade_the_logger_to_a_notifier_when_adding_a_notifier_by_default()
    {
        $notifier = new DummyNotifier(new ConnectionHandlerImpl());

        TestCase::assertInstanceOf(NullLogger::class, $notifier->getLogger());

        $sut = new NotificationCenter();

        TestCase::assertInstanceOf(NullLogger::class, $sut->getLogger());

        $sut->setLogger(new DummyLogger());

        TestCase::assertInstanceOf(DummyLogger::class, $sut->getLogger());

        $sut->addNotifier($notifier);

        TestCase::assertInstanceOf(DummyLogger::class, $notifier->getLogger());
    }

    /** @test */
    public function it_should_not_cascade_the_logger_to_a_notifier_when_adding_a_notifier_if_we_disable_logger_setting()
    {
        $notifier = new DummyNotifier(new ConnectionHandlerImpl());

        TestCase::assertInstanceOf(NullLogger::class, $notifier->getLogger());

        $sut = new NotificationCenter();

        TestCase::assertInstanceOf(NullLogger::class, $sut->getLogger());

        $sut->setLogger(new DummyLogger());

        TestCase::assertInstanceOf(DummyLogger::class, $sut->getLogger());

        $sut->addNotifier($notifier, false);

        TestCase::assertInstanceOf(NullLogger::class, $notifier->getLogger());
    }

    /** @test */
    public function it_should_return_the_correct_notifier_depending_on_the_message()
    {
        $appleNotifier = new AppleNotifier(new ConnectionHandlerImpl());
        $androidNotifier = new AndroidNotifier(new ConnectionHandlerImpl());

        $appleMessage = $this->prophesize(AppleMessage::class);
        $androidMessage = $this->prophesize(AndroidMessage::class);

        $sut = new NotificationCenter();
        $sut->addNotifier($appleNotifier);
        $sut->addNotifier($androidNotifier);

        TestCase::assertSame($appleNotifier, $sut->getNotifierForMessage($appleMessage->reveal()));
        TestCase::assertSame($androidNotifier, $sut->getNotifierForMessage($androidMessage->reveal()));
    }

    /** @test */
    public function it_should_throw_an_exception_if_no_notifier_is_found_for_a_message()
    {
        $this->expectException(NotifierNotFoundException::class);

        $appleMessage = $this->prophesize(AppleMessage::class);

        $sut = new NotificationCenter();

        $sut->getNotifierForMessage($appleMessage->reveal());
    }

    /** @test */
    public function it_should_send_the_message_to_the_correct_notifier()
    {
        $appleNotifier = new AppleNotifier(new ConnectionHandlerImpl());
        $androidNotifier = new AndroidNotifier(new ConnectionHandlerImpl());
        $dummyNotifier = new DummyNotifier(new ConnectionHandlerImpl());

        $sut = new NotificationCenter();
        $sut->addNotifier($appleNotifier);
        $sut->addNotifier($androidNotifier);
        $sut->addNotifier($dummyNotifier);

        $message = new DummyMessage('we', 'dont care');

        $sut->send($message);

        TestCase::assertTrue($dummyNotifier->sendMessageWasCalled());
    }

    /** @test */
    public function it_should_queue_the_message_on_the_correct_notifier()
    {
        $appleNotifier = new AppleNotifier(new ConnectionHandlerImpl());
        $androidNotifier = new AndroidNotifier(new ConnectionHandlerImpl());

        $connectionHandler = new ConnectionHandlerImpl();
        $connectionHandler->addConnection(DummyConnection::withDefault());
        $dummyNotifier = new DummyNotifier($connectionHandler);

        $sut = new NotificationCenter();
        $sut->addNotifier($appleNotifier);
        $sut->addNotifier($androidNotifier);
        $sut->addNotifier($dummyNotifier);

        $message = new DummyMessage('we', 'dont care');

        $sut->queue($message);

        TestCase::assertTrue($dummyNotifier->queueMessageWasCalled());
    }

    /** @test */
    public function it_should_flush_queues()
    {
        $notifier = $this->prophesize(Notifier::class);

        $sut = new NotificationCenter();
        $sut->addNotifier($notifier->reveal());

        $sut->flushQueue();

        $notifier->flushQueue(null)->shouldHaveBeenCalled();
    }

    /** @test */
    public function it_should_flush_queues_with_connection_name_when_specified()
    {
        $notifier = $this->prophesize(Notifier::class);

        $sut = new NotificationCenter();
        $sut->addNotifier($notifier->reveal());

        $sut->flushQueue('test');

        $notifier->flushQueue('test')->shouldHaveBeenCalled();
    }
}
