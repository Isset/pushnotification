<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Tests\Core\Message;

use IssetBV\PushNotification\Core\Message\MessageEnvelope;
use IssetBV\PushNotification\Core\Message\MessageEnvelopeQueueImpl;
use IssetBV\PushNotification\Tests\Type\Dummy\DummyMessageEnvelope;
use PhpOption\None;
use PhpOption\Option;
use PHPUnit\Framework\TestCase;

class MessageEnvelopeQueueImplTest extends TestCase
{
    /** @test */
    public function it_should_default_to_an_empty_queue()
    {
        $sut = new MessageEnvelopeQueueImpl();

        TestCase::assertEmpty($sut->getQueue());
    }

    /** @test */
    public function it_should_allow_adding_of_message_envelopes_to_the_queue()
    {
        $messageEnvelope = DummyMessageEnvelope::withSimpleMessage();

        $sut = new MessageEnvelopeQueueImpl();
        $sut->add($messageEnvelope);
        $sut->add($messageEnvelope);

        TestCase::assertCount(2, $sut->getQueue());
    }

    /** @test */
    public function it_should_tell_us_if_the_queue_is_empty()
    {
        $messageEnvelope = DummyMessageEnvelope::withSimpleMessage();

        $sut = new MessageEnvelopeQueueImpl();
        TestCase::assertTrue($sut->isEmpty());

        $sut->add($messageEnvelope);
        TestCase::assertFalse($sut->isEmpty());
    }

    /** @test */
    public function it_should_empty_the_queue_when_we_reset()
    {
        $messageEnvelope = DummyMessageEnvelope::withSimpleMessage();

        $sut = new MessageEnvelopeQueueImpl();
        TestCase::assertTrue($sut->isEmpty());

        $sut->add($messageEnvelope);
        TestCase::assertFalse($sut->isEmpty());

        $sut->clear();
        TestCase::assertTrue($sut->isEmpty());
    }

    /** @test */
    public function it_should_tell_us_if_the_queue_has_a_message_with_a_specific_identifier()
    {
        $messageEnvelope = DummyMessageEnvelope::withMessageWithIdentifier('test-identifier');

        $sut = new MessageEnvelopeQueueImpl();
        TestCase::assertFalse($sut->has('test-identifier'));

        $sut->add($messageEnvelope);
        TestCase::assertTrue($sut->has('test-identifier'));
    }

    /** @test */
    public function it_should_be_able_to_remove_a_message_with_a_specific_identifier()
    {
        $messageEnvelope = DummyMessageEnvelope::withMessageWithIdentifier('test-identifier');

        $sut = new MessageEnvelopeQueueImpl();
        $sut->add($messageEnvelope);
        TestCase::assertTrue($sut->has('test-identifier'));

        $sut->remove('test-identifier');
        TestCase::assertFalse($sut->has('test-identifier'));
    }

    /** @test */
    public function it_should_pop_the_item_from_the_queue_when_we_remove_an_item()
    {
        $messageEnvelope = DummyMessageEnvelope::withMessageWithIdentifier('test-identifier');
        $sut = new MessageEnvelopeQueueImpl();
        $sut->add($messageEnvelope);
        TestCase::assertTrue($sut->has('test-identifier'));

        $result = $sut->remove('test-identifier');
        TestCase::assertInstanceOf(Option::class, $result);
    }

    /** @test */
    public function it_should_return_an_optional_of_none_when_try_to_remove_an_item_not_on_the_queue()
    {
        $sut = new MessageEnvelopeQueueImpl();
        $result = $sut->remove('test-identifier');
        TestCase::assertInstanceOf(None::class, $result);
    }

    /** @test */
    public function it_should_accept_a_closure_and_apply_it_to_the_queue()
    {
        $messageEnvelope = DummyMessageEnvelope::withMessageWithIdentifier('test-identifier');
        $messageEnvelope->setState(MessageEnvelope::PENDING);

        TestCase::assertSame(MessageEnvelope::PENDING, $messageEnvelope->getState());

        $sut = new MessageEnvelopeQueueImpl();
        $sut->add($messageEnvelope);

        $sut->traverseWith(function (MessageEnvelope $messageEnvelope) {
            $messageEnvelope->setState(MessageEnvelope::SUCCESS);
        });

        $queue = $sut->getQueue();

        foreach ($queue as $item) {
            TestCase::assertSame(MessageEnvelope::SUCCESS, $item->getState());
        }
    }

    /** @test */
    public function it_should_split_the_queue_including_the_identifier()
    {
        $messageEnvelope1 = DummyMessageEnvelope::withMessageWithIdentifier('test-identifier1');
        $messageEnvelope2 = DummyMessageEnvelope::withMessageWithIdentifier('test-identifier2');
        $messageEnvelope3 = DummyMessageEnvelope::withMessageWithIdentifier('test-identifier3');

        $sut = new MessageEnvelopeQueueImpl();
        $sut->add($messageEnvelope1);
        $sut->add($messageEnvelope2);
        $sut->add($messageEnvelope3);

        $newQueue = $sut->split('test-identifier2');

        TestCase::assertSame([$messageEnvelope1, $messageEnvelope2], $newQueue->getQueue());
        TestCase::assertSame([$messageEnvelope3], $sut->getQueue());
    }
}
