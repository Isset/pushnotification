<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Tests\Core\Connection;

use IssetBV\PushNotification\Core\Connection\ConnectionResponseImpl;
use PHPUnit\Framework\TestCase;

class ConnectionResponseImplTest extends TestCase
{
    /** @test */
    public function it_should_default_to_a_successful_response()
    {
        $sut = new ConnectionResponseImpl();

        TestCase::assertTrue($sut->isSuccess());
    }

    /** @test */
    public function it_should_allow_setting_of_success()
    {
        $sut = new ConnectionResponseImpl();

        TestCase::assertTrue($sut->isSuccess());

        $sut->setSuccess(false);

        TestCase::assertFalse($sut->isSuccess());
    }

    /** @test */
    public function it_should_default_to_an_empty_response()
    {
        $sut = new ConnectionResponseImpl();

        TestCase::assertNull($sut->getResponse());
    }

    /** @test */
    public function it_should_allow_setting_of_response()
    {
        $sut = new ConnectionResponseImpl();

        TestCase::assertNull($sut->getResponse());

        $sut->setResponse('a response');

        TestCase::assertSame('a response', $sut->getResponse());
    }

    /** @test */
    public function it_should_mark_as_unsuccessful_when_an_error_response_is_set()
    {
        $sut = new ConnectionResponseImpl();

        TestCase::assertNull($sut->getResponse());
        TestCase::assertTrue($sut->isSuccess());

        $sut->setErrorResponse('something went wrong');

        TestCase::assertFalse($sut->isSuccess());
        TestCase::assertSame('something went wrong', $sut->getResponse());
    }
}
