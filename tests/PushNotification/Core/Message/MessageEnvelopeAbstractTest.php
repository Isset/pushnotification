<?php

namespace IssetBV\PushNotification\Tests\Core\Message;

use IssetBV\PushNotification\Core\Connection\ConnectionResponseImpl;
use IssetBV\PushNotification\Core\Message\MessageEnvelope;
use IssetBV\PushNotification\Tests\Type\Dummy\DummyMessage;
use IssetBV\PushNotification\Tests\Type\Dummy\DummyMessageEnvelope;
use PhpOption\None;
use PhpOption\Option;
use PHPUnit\Framework\TestCase;

class MessageEnvelopeAbstractTest extends TestCase
{
    /** @test */
    public function it_should_default_to_pending_state()
    {
        $sut = new DummyMessageEnvelope(new DummyMessage('we', 'dont care'));

        TestCase::assertSame(MessageEnvelope::PENDING, $sut->getState());
    }

    /** @test */
    public function it_should_expose_accessors_for_state()
    {
        $sut = new DummyMessageEnvelope(new DummyMessage('we', 'dont care'));

        TestCase::assertSame(MessageEnvelope::PENDING, $sut->getState());

        $sut->setState(MessageEnvelope::SUCCESS);

        TestCase::assertSame(MessageEnvelope::SUCCESS, $sut->getState());
    }

    /** @test */
    public function it_should_be_able_to_retrieve_the_message()
    {
        $sut = new DummyMessageEnvelope(new DummyMessage('we', 'dont care'));

        TestCase::assertInstanceOf(DummyMessage::class, $sut->getMessage());
    }

    /** @test */
    public function it_should_return_an_optional_response_since_we_cant_guarantee_there_will_always_be_a_response()
    {
        $sut = new DummyMessageEnvelope(new DummyMessage('we', 'dont care'));

        TestCase::assertInstanceOf(Option::class, $sut->getResponse());
    }

    /** @test */
    public function it_should_allow_changing_of_the_response()
    {
        $sut = new DummyMessageEnvelope(new DummyMessage('we', 'dont care'));

        $emptyResponse = $sut->getResponse();

        TestCase::assertInstanceOf(None::class, $emptyResponse);

        $sut->setResponse(new ConnectionResponseImpl());

        $response = $sut->getResponse();

        TestCase::assertInstanceOf(Option::class, $response);
        TestCase::assertInstanceOf(ConnectionResponseImpl::class, $response->get());
    }
}
