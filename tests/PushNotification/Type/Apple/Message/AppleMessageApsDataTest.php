<?php

namespace IssetBV\PushNotification\Tests\Type\Apple\Message;

use IssetBV\PushNotification\Type\Apple\Message\AppleMessageApsData;
use PHPUnit\Framework\TestCase;

class AppleMessageApsDataTest extends TestCase
{
    /** @var AppleMessageApsData */
    private $apsData;

    public function setUp()
    {
        $this->apsData = new AppleMessageApsData();
    }

    /** @test */
    public function it_should_default_values_to_null()
    {
        TestCase::assertNull($this->apsData->getAlert());
        TestCase::assertNull($this->apsData->getBadge());
        TestCase::assertNull($this->apsData->getCategory());
        TestCase::assertNull($this->apsData->getSound());
        TestCase::assertFalse($this->apsData->isContentAvailable());
        TestCase::assertSame([], $this->apsData->toArray());
    }

    /** @test */
    public function it_should_only_add_values_to_output_when_set()
    {
        TestCase::assertArrayNotHasKey('alert', $this->apsData->toArray());
        $this->apsData->setAlert('test-alert');
        TestCase::assertArraySubset(['alert' => 'test-alert'], $this->apsData->toArray());

        TestCase::assertArrayNotHasKey('badge', $this->apsData->toArray());
        $this->apsData->setBadge(1);
        TestCase::assertArraySubset(['badge' => 1], $this->apsData->toArray());

        TestCase::assertArrayNotHasKey('sound', $this->apsData->toArray());
        $this->apsData->setSound('test-sound');
        TestCase::assertArraySubset(['sound' => 'test-sound'], $this->apsData->toArray());

        TestCase::assertArrayNotHasKey('category', $this->apsData->toArray());
        $this->apsData->setCategory('test-category');
        TestCase::assertArraySubset(['category' => 'test-category'], $this->apsData->toArray());

        TestCase::assertArrayNotHasKey('content-available', $this->apsData->toArray());
        $this->apsData->setContentAvailable(true);
        TestCase::assertArraySubset(['content-available' => 1], $this->apsData->toArray());
    }
}
