<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Tests\Type\Android;

use IssetBV\PushNotification\Core\Connection\ConnectionHandlerImpl;
use IssetBV\PushNotification\Core\NotifierAbstract;
use IssetBV\PushNotification\Tests\Core\NotifierAbstractTest;
use IssetBV\PushNotification\Tests\Type\Dummy\DummyMessage;
use IssetBV\PushNotification\Type\Android\AndroidConnection;
use IssetBV\PushNotification\Type\Android\AndroidNotifier;
use IssetBV\PushNotification\Type\Android\Message\AndroidMessage;
use IssetBV\PushNotification\Type\Android\Message\AndroidMessageEnvelope;
use PHPUnit\Framework\TestCase;

class AndroidNotifierTest extends NotifierAbstractTest
{
    /** @test */
    public function it_should_only_handle_android_messages()
    {
        $dummyMessage = DummyMessage::simple();
        $androidMessage = new AndroidMessage('test');

        TestCase::assertFalse($this->notifier->handles($dummyMessage));
        TestCase::assertTrue($this->notifier->handles($androidMessage));
    }

    protected function getNotifierUnderTest($connectionHandler = null): NotifierAbstract
    {
        $connectionHandler = $connectionHandler ?? new ConnectionHandlerImpl();

        return new AndroidNotifier($connectionHandler);
    }

    protected function getNotifierUnderTestConnection()
    {
        return AndroidConnection::class;
    }

    protected function getNotifierUnderTestMessageEnvelope()
    {
        return AndroidMessageEnvelope::class;
    }

    protected function getNotifierUnderTestMessage()
    {
        return new AndroidMessage('test');
    }
}
