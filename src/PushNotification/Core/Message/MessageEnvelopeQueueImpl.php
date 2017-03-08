<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Core\Message;

use Closure;
use LogicException;
use PhpOption\None;
use PhpOption\Option;

/**
 * Class MessageEnvelopeQueueImpl.
 */
class MessageEnvelopeQueueImpl implements MessageEnvelopeQueue
{
    /**
     * @var MessageEnvelope[]
     */
    private $queue = [];

    /**
     * @return MessageEnvelope[]
     */
    public function getQueue(): array
    {
        return $this->queue;
    }

    /**
     * @param MessageEnvelope $message
     */
    public function add(MessageEnvelope $message)
    {
        $this->queue[] = $message;
    }

    /**
     * @param string $identifier
     *
     * @throws LogicException
     *
     * @return MessageEnvelopeQueue
     */
    public function split($identifier): MessageEnvelopeQueue
    {
        $return = new self();
        $items = $this->queue;
        $this->reset();
        $found = false;
        foreach ($items as $id => $item) {
            if (!$found) {
                $return->add($item);
                if ($item->getMessage()->getIdentifier() === $identifier) {
                    $found = true;
                }
            } else {
                $this->add($item);
                $newQueue[] = $item;
            }
        }

        return $return;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return count($this->queue) === 0;
    }

    public function reset()
    {
        $this->queue = [];
    }

    /**
     * @param Closure $callable
     */
    public function traverseWith(Closure $callable)
    {
        foreach ($this->queue as $item) {
            $callable($item);
        }
    }

    /**
     * @param $identifier
     *
     * @return Option<MessageEnvelope>
     */
    public function remove($identifier): Option
    {
        foreach ($this->queue as $key => $item) {
            if ($item->getMessage()->getIdentifier() === $identifier) {
                unset($this->queue[$key]);

                return Option::fromValue($item);
            }
        }

        return None::create();
    }

    /**
     * @param $identifier
     *
     * @return bool
     */
    public function has($identifier): bool
    {
        foreach ($this->queue as $item) {
            if ($item->getMessage()->getIdentifier() === $identifier) {
                return true;
            }
        }

        return false;
    }
}
