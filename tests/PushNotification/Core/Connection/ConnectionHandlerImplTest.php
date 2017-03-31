<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Tests\Core\Connection;

use IssetBV\PushNotification\Core\Connection\ConnectionHandlerExceptionImpl;
use IssetBV\PushNotification\Core\Connection\ConnectionHandlerImpl;
use IssetBV\PushNotification\Tests\Type\Dummy\DummyConnection;
use IssetBV\PushNotification\Tests\Type\Dummy\DummyLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ConnectionHandlerImplTest extends TestCase
{
    /** @test */
    public function it_should_throw_an_exception_when_retrieving_the_default_connection_if_no_default_connection_was_specified()
    {
        $this->expectException(ConnectionHandlerExceptionImpl::class);
        $this->expectExceptionMessage('No default connection found');

        $sut = new ConnectionHandlerImpl();
        $sut->getDefaultConnection();
    }

    /** @test */
    public function it_should_get_the_default_connection_when_one_is_specified()
    {
        $connection = DummyConnection::withDefault();

        $sut = new ConnectionHandlerImpl();
        $sut->addConnection($connection);

        $result = $sut->getDefaultConnection();

        TestCase::assertSame($connection, $result);
    }

    /** @test */
    public function it_should_throw_an_exception_when_no_connection_type_was_given_and_no_default_connection_is_specified()
    {
        $this->expectException(ConnectionHandlerExceptionImpl::class);
        $this->expectExceptionMessage('No default connection found');

        $sut = new ConnectionHandlerImpl();
        $sut->getConnection();
    }

    /** @test */
    public function it_should_get_the_default_connection_if_no_connection_type_was_given_and_a_default_connection_is_specified()
    {
        $connection = DummyConnection::withDefault();

        $sut = new ConnectionHandlerImpl();
        $sut->addConnection($connection);

        $result = $sut->getConnection();

        TestCase::assertSame($connection, $result);
    }

    /** @test */
    public function it_should_throw_an_exception_when_the_specified_type_is_not_added()
    {
        $this->expectException(ConnectionHandlerExceptionImpl::class);
        $this->expectExceptionMessage('Connection not found for type: non_existing_type');

        $sut = new ConnectionHandlerImpl();

        $result = $sut->getConnection('non_existing_type');
    }

    /** @test */
    public function it_should_get_the_connection_with_a_specific_type()
    {
        $specificTypeConnection = new DummyConnection('specific_type', false);

        $sut = new ConnectionHandlerImpl();
        $sut->addConnection(DummyConnection::withDefault());
        $sut->addConnection($specificTypeConnection);

        $result = $sut->getConnection('specific_type');

        TestCase::assertSame($specificTypeConnection, $result);
    }

    /** @test */
    public function it_should_be_able_to_override_the_default_connection_with_a_type_connection()
    {
        $defaultConnection = DummyConnection::withDefault();
        $specificTypeConnection = new DummyConnection('specific_type', false);

        $sut = new ConnectionHandlerImpl();
        $sut->addConnection($defaultConnection);
        $sut->addConnection($specificTypeConnection);

        TestCase::assertSame($defaultConnection, $sut->getConnection());
        TestCase::assertSame($defaultConnection, $sut->getDefaultConnection());

        $sut->setDefaultConnectionByType('specific_type');

        TestCase::assertSame($specificTypeConnection, $sut->getConnection());
        TestCase::assertSame($specificTypeConnection, $sut->getDefaultConnection());
    }

    /** @test */
    public function it_should_cascade_a_logger_to_all_the_connections()
    {
        $defaultConnection = DummyConnection::withDefault();
        $specificTypeConnection = new DummyConnection('specific_type', false);

        $sut = new ConnectionHandlerImpl();
        $sut->addConnection($defaultConnection);
        $sut->addConnection($specificTypeConnection);

        TestCase::assertInstanceOf(NullLogger::class, $defaultConnection->getLogger());
        TestCase::assertInstanceOf(NullLogger::class, $specificTypeConnection->getLogger());
        TestCase::assertInstanceOf(NullLogger::class, $sut->getLogger());

        $sut->setLogger(new DummyLogger());

        TestCase::assertInstanceOf(DummyLogger::class, $defaultConnection->getLogger());
        TestCase::assertInstanceOf(DummyLogger::class, $specificTypeConnection->getLogger());
        TestCase::assertInstanceOf(DummyLogger::class, $sut->getLogger());
    }
}
