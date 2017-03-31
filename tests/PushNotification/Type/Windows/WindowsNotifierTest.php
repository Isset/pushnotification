<?php

namespace IssetBV\PushNotification\Tests\Type\Windows;

use IssetBV\PushNotification\Core\Connection\ConnectionHandlerImpl;
use IssetBV\PushNotification\Core\NotifierAbstract;
use IssetBV\PushNotification\Tests\Core\NotifierAbstractTest;
use IssetBV\PushNotification\Tests\Type\Dummy\DummyMessage;
use IssetBV\PushNotification\Type\Windows\Message\WindowsMessage;
use IssetBV\PushNotification\Type\Windows\Message\WindowsMessageEnvelope;
use IssetBV\PushNotification\Type\Windows\WindowsConnection;
use IssetBV\PushNotification\Type\Windows\WindowsNotifier;
use PHPUnit\Framework\TestCase;

class WindowsNotifierTest extends NotifierAbstractTest
{
    /** @test */
    public function it_should_only_handle_windows_messages()
    {
        $dummyMessage = DummyMessage::simple();
        $message = new WindowsMessage('test');

        TestCase::assertFalse($this->notifier->handles($dummyMessage));
        TestCase::assertTrue($this->notifier->handles($message));
    }

    protected function getNotifierUnderTest($connectionHandler = null): NotifierAbstract
    {
        $connectionHandler = $connectionHandler ?? new ConnectionHandlerImpl();

        return new WindowsNotifier($connectionHandler);
    }

    protected function getNotifierUnderTestConnection()
    {
        return WindowsConnection::class;
    }

    protected function getNotifierUnderTestMessageEnvelope()
    {
        return WindowsMessageEnvelope::class;
    }

    protected function getNotifierUnderTestMessage()
    {
        return new WindowsMessage('test');
    }
}
