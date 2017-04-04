<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Tests\Type\Dummy;

use IssetBV\PushNotification\Core\Connection\ConnectionException;
use IssetBV\PushNotification\Core\Connection\ConnectionHandlerException;
use IssetBV\PushNotification\Core\Exception\NotifyFailedException;
use IssetBV\PushNotification\Core\Message\Message;
use IssetBV\PushNotification\Core\Message\MessageEnvelope;
use IssetBV\PushNotification\Core\Message\MessageEnvelopeQueue;
use IssetBV\PushNotification\Core\NotifierAbstract;

final class DummyNotifier extends NotifierAbstract
{
    private $sendWasCalled = false;
    private $queueWasCalled = false;
    private $flushWasCalled = 0;

    /**
     * @param Message $message
     *
     * @return bool
     */
    public function handles(Message $message): bool
    {
        return $message instanceof DummyMessage;
    }

    public function sendMessageWasCalled(): bool
    {
        return $this->sendWasCalled;
    }

    public function queueMessageWasCalled(): bool
    {
        return $this->queueWasCalled;
    }

    public function flushQueueItemWasCalledTimes(): int
    {
        return $this->flushWasCalled;
    }

    /**
     * @param Message $message
     *
     * @return MessageEnvelope
     */
    protected function createMessageEnvelope(Message $message): MessageEnvelope
    {
        return new DummyMessageEnvelope($message);
    }

    /**
     * @param Message $message
     * @param string|null $connectionName
     *
     * @return MessageEnvelope
     */
    protected function addToQueue(Message $message, string $connectionName = null): MessageEnvelope
    {
        $this->queueWasCalled = true;

        return parent::addToQueue($message, $connectionName);
    }

    /**
     * @param Message $message
     * @param string $connectionName
     *
     * @return MessageEnvelope
     */
    protected function sendMessage(Message $message, string $connectionName = null): MessageEnvelope
    {
        $this->sendWasCalled = true;

        return DummyMessageEnvelope::withSimpleMessage();
    }

    /**
     * @param string $connectionName
     * @param MessageEnvelopeQueue $queue
     *
     * @throws NotifyFailedException
     * @throws ConnectionHandlerException
     * @throws ConnectionException
     */
    protected function flushQueueItem(string $connectionName, MessageEnvelopeQueue $queue)
    {
        ++$this->flushWasCalled;
    }
}
