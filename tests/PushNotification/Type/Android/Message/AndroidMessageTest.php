<?php

namespace IssetBV\PushNotification\Tests\Type\Android\Message;

use IssetBV\PushNotification\Type\Android\Message\AndroidMessage;
use PHPUnit\Framework\TestCase;

class AndroidMessageTest extends TestCase
{
    /** @test */
    public function it_should_have_an_identifier()
    {
        $sut = new AndroidMessage('identifier');

        TestCase::assertSame('identifier', $sut->getIdentifier());
    }

    /** @test */
    public function it_should_have_the_identifier_as_the_to_field()
    {
        $sut = new AndroidMessage('identifier');

        TestCase::assertTrue($sut->payloadContainsKey('to'));
    }

    /** @test */
    public function it_should_be_change_the_payload()
    {
        $sut = new AndroidMessage('identifier');

        TestCase::assertTrue($sut->payloadContainsKey('to'));
        TestCase::assertFalse($sut->payloadContainsKey('i_dont_exist'));

        $sut->addToPayload('i_dont_exist', 'we dont care');

        TestCase::assertTrue($sut->payloadContainsKey('i_dont_exist'));
    }

    /** @test */
    public function it_should_return_the_entire_payload_as_message()
    {
        $sut = new AndroidMessage('identifier');

        TestCase::assertSame(
            ['to' => 'identifier'],
            $sut->getMessage()
        );

        $sut->addToPayload('new key', 'we dont care');

        TestCase::assertSame(
            ['to' => 'identifier', 'new key' => 'we dont care'],
            $sut->getMessage()
        );
    }
}
