<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Core;

use IssetBV\PushNotification\Core\Message\Message;
use IssetBV\PushNotification\Core\Message\MessageEnvelope;
use Psr\Log\LoggerAwareInterface;

/**
 * Interface Notifier.
 */
interface Notifier extends LoggerAwareInterface
{
    /**
     * @param Message $message
     * @param string $connectionName
     *
     * @return MessageEnvelope
     */
    public function send(Message $message, string $connectionName = null): MessageEnvelope;

    /**
     * @param Message $message
     * @param string|null $connectionName
     *
     * @return MessageEnvelope
     */
    public function queue(Message $message, string $connectionName = null): MessageEnvelope;

    /**
     * @param Message $message
     *
     * @return bool
     */
    public function handles(Message $message): bool;

    /**
     * Flushes the queue to the notifier queues.
     *
     * @param string $connectionName
     */
    public function flushQueue(string $connectionName = null);
}
