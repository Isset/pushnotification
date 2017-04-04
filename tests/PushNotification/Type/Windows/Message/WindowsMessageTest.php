<?php

namespace IssetBV\PushNotification\Tests\Type\Windows\Message;

use IssetBV\PushNotification\Type\Windows\Message\WindowsMessage;
use PHPUnit\Framework\TestCase;

class WindowsMessageTest extends TestCase
{
    /** @test */
    public function it_should_have_an_identifier()
    {
        $sut = new WindowsMessage('identifier');

        TestCase::assertSame('identifier', $sut->getIdentifier());
    }

    /** @test */
    public function it_should_be_able_to_add_key_values_to_the_payload()
    {
        $sut = new WindowsMessage('identifier');

        TestCase::assertFalse($sut->payloadContainsKey('i_dont_exist'));

        $sut->addToPayload('i_dont_exist', 'we dont care');

        TestCase::assertTrue($sut->payloadContainsKey('i_dont_exist'));
    }

    /** @test */
    public function it_should_return_the_entire_payload_as_message()
    {
        $sut = new WindowsMessage('identifier');

        TestCase::assertSame(
            [],
            $sut->getMessage()
        );

        $sut->addToPayload('new key', 'we dont care');

        TestCase::assertSame(
            ['new key' => 'we dont care'],
            $sut->getMessage()
        );
    }
}
