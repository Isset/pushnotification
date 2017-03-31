<?php

namespace IssetBV\PushNotification\Tests\Type\Apple\Message;

use DateTime;
use IssetBV\PushNotification\Type\Apple\Message\AppleMessageAps;
use IssetBV\PushNotification\Type\Apple\Message\AppleMessageApsData;
use PHPUnit\Framework\TestCase;

class AppleMessageApsTest extends TestCase
{
    /** @var AppleMessageAps */
    private $message;

    public function setUp()
    {
        $this->message = new AppleMessageAps('device_identifier');
    }

    /** @test */
    public function it_should_build_correctly()
    {
        TestCase::assertSame('device_identifier', $this->message->getDeviceToken());
    }

    /** @test */
    public function it_should_up_the_internal_counter_whenever_a_new_message_is_created_to_guarantee_uniqueness_within_the_session()
    {
        $message1 = new AppleMessageAps('device_identifier');
        $message2 = new AppleMessageAps('another_device');
        $message3 = new AppleMessageAps('yet_another_device');

        TestCase::assertSame($message1->getIdentifier() + 1, $message2->getIdentifier());
        TestCase::assertSame($message2->getIdentifier() + 1, $message3->getIdentifier());
    }

    /** @test */
    public function it_should_default_the_expires_at_to_zero()
    {
        TestCase::assertSame(0, $this->message->getExpiresAt());
    }

    /** @test */
    public function it_should_get_expires_at_as_timestamp_and_set_the_expires_at_as_datetime()
    {
        $dateTime = new DateTime('2017-01-01 13:37');

        TestCase::assertSame(0, $this->message->getExpiresAt());

        $this->message->setExpiresAt($dateTime);

        TestCase::assertSame((int) $dateTime->format('U'), $this->message->getExpiresAt());
    }

    /** @test */
    public function it_should_create_a_new_apple_message_aps_data_when_it_was_not_explicitly_set()
    {
        TestCase::assertInstanceOf(AppleMessageApsData::class, $this->message->getAps());
    }

    /** @test */
    public function it_should_get_and_set_apple_message_aps_data()
    {
        $apsData = new AppleMessageApsData();

        TestCase::assertNotSame($apsData, $this->message->getAps());

        $this->message->setAppleMessageAps($apsData);

        TestCase::assertSame($apsData, $this->message->getAps());
    }

    /** @test */
    public function it_should_get_and_set_message_payload()
    {
        TestCase::assertFalse($this->message->payloadContainsKey('test'));

        $this->message->addToPayload('test', 'we dont care');

        TestCase::assertTrue($this->message->payloadContainsKey('test'));
    }

    /** @test */
    public function it_should_default_to_an_empty_message()
    {
        TestCase::assertSame([], $this->message->getMessage());
    }

    /** @test */
    public function it_should_create_a_message_from_payload()
    {
        TestCase::assertSame([], $this->message->getMessage());
        TestCase::assertFalse($this->message->payloadContainsKey('test'));

        $this->message->addToPayload('test', 'we dont care');

        TestCase::assertSame(['test' => 'we dont care'], $this->message->getMessage());
    }

    /** @test */
    public function it_should_add_the_aps_data_to_the_payload_if_it_is_set()
    {
        TestCase::assertSame([], $this->message->getMessage());
        TestCase::assertFalse($this->message->payloadContainsKey('test'));

        $this->message->addToPayload('test', 'we dont care');

        TestCase::assertSame(['test' => 'we dont care'], $this->message->getMessage());

        $apsData = new AppleMessageApsData();

        $this->message->setAppleMessageAps($apsData);

        TestCase::assertSame(['test' => 'we dont care', 'aps' => []], $this->message->getMessage());
    }
}
