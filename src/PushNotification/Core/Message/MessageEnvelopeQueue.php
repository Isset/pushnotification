<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Core\Message;

use Closure;
use PhpOption\Option;

/**
 * Interface MessageEnvelopeQueue.
 */
interface MessageEnvelopeQueue
{
    /**
     * @return MessageEnvelope[]
     */
    public function getQueue(): array;

    /**
     * @param MessageEnvelope $message
     */
    public function add(MessageEnvelope $message);

    /**
     * @param $identifier
     *
     * @return bool
     */
    public function has($identifier): bool;

    /**
     * @param string $identifier
     *
     * @return MessageEnvelopeQueue
     */
    public function split($identifier): MessageEnvelopeQueue;

    /**
     * @param Closure $callable
     */
    public function traverseWith(Closure $callable);

    /**
     * @param $identifier
     *
     * @return Option<MessageEnvelope>
     */
    public function remove($identifier): Option;

    /**
     * @return bool
     */
    public function isEmpty(): bool;

    public function reset();
}
