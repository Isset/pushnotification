<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Type\Android;

use IssetBV\PushNotification\Core\Connection\ConnectionException;
use IssetBV\PushNotification\Core\Connection\ConnectionHandlerException;
use IssetBV\PushNotification\Core\Message\Message;
use IssetBV\PushNotification\Core\Message\MessageEnvelope;
use IssetBV\PushNotification\Core\Message\MessageEnvelopeQueue;
use IssetBV\PushNotification\Core\NotifierAbstract;
use IssetBV\PushNotification\Type\Android\Message\AndroidMessage;
use IssetBV\PushNotification\Type\Android\Message\AndroidMessageEnvelope;

/**
 * Class AndroidNotifier.
 */
class AndroidNotifier extends NotifierAbstract
{
    /**
     * @param Message $message
     *
     * @return bool
     */
    public function handles(Message $message): bool
    {
        return $message instanceof AndroidMessage;
    }

    /**
     * @param Message $message
     * @param string $connectionName
     *
     * @throws ConnectionHandlerException
     * @throws ConnectionException
     *
     * @return MessageEnvelope
     */
    protected function sendMessage(Message $message, string $connectionName = null): MessageEnvelope
    {
        /* @var AndroidMessage $message */
        $connection = $this->getConnectionHandler()->getConnection($connectionName);
        /* @var AndroidConnection $connection */
        $messageEnvelope = $this->createMessageEnvelope($message);
        /* @var AndroidMessageEnvelope $messageEnvelope */
        $this->sendMessageEnvelope($connection, $messageEnvelope);

        return $messageEnvelope;
    }

    /**
     * @param Message $message
     *
     * @return MessageEnvelope
     */
    protected function createMessageEnvelope(Message $message): MessageEnvelope
    {
        /* @var AndroidMessage $message */
        return new AndroidMessageEnvelope($message);
    }

    /**
     * @param string $connectionName
     * @param MessageEnvelopeQueue $queue
     *
     * @throws ConnectionHandlerException
     * @throws ConnectionException
     */
    protected function flushQueueItem(string $connectionName = null, MessageEnvelopeQueue $queue)
    {
        if ($queue->isEmpty()) {
            return;
        }
        $connection = $this->getConnectionHandler()->getConnection($connectionName);
        /* @var AndroidConnection $connection */
        foreach ($queue->getQueue() as $messageEnvelope) {
            /* @var AndroidMessageEnvelope $messageEnvelope */
            $this->sendMessageEnvelope($connection, $messageEnvelope);
        }
        $queue->clear();
    }

    /**
     * @param AndroidConnection $connection
     * @param AndroidMessageEnvelope $messageEnvelope
     */
    private function sendMessageEnvelope(AndroidConnection $connection, AndroidMessageEnvelope $messageEnvelope)
    {
        $response = $connection->sendAndReceive($messageEnvelope->getMessage());
        $messageEnvelope->setResponse($response);
        if ($response->isSuccess()) {
            $messageEnvelope->setState(MessageEnvelope::SUCCESS);
        } else {
            $messageEnvelope->setState(MessageEnvelope::FAILED);
        }
    }
}
