<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Type\Windows;

use IssetBV\PushNotification\Core\Connection\ConnectionException;
use IssetBV\PushNotification\Core\Connection\ConnectionHandlerException;
use IssetBV\PushNotification\Core\Message\Message;
use IssetBV\PushNotification\Core\Message\MessageEnvelope;
use IssetBV\PushNotification\Core\Message\MessageEnvelopeQueue;
use IssetBV\PushNotification\Core\NotifierAbstract;
use IssetBV\PushNotification\Type\Windows\Message\WindowsMessage;
use IssetBV\PushNotification\Type\Windows\Message\WindowsMessageEnvelope;

/**
 * Class WindowsNotifier.
 */
class WindowsNotifier extends NotifierAbstract
{
    /**
     * @param Message $message
     *
     * @return bool
     */
    public function handles(Message $message): bool
    {
        return $message instanceof WindowsMessage;
    }

    /**
     * @param Message $message
     *
     * @return MessageEnvelope
     */
    protected function createMessageEnvelope(Message $message): MessageEnvelope
    {
        /* @var WindowsMessage $message */
        return new WindowsMessageEnvelope($message);
    }

    /**
     * @param Message $message
     * @param string $connectionName
     *
     * @throws ConnectionException
     * @throws ConnectionHandlerException
     *
     * @return MessageEnvelope
     */
    protected function sendMessage(Message $message, string $connectionName = null): MessageEnvelope
    {
        $connection = $this->getConnectionHandler()->getConnection($connectionName);
        /* @var WindowsConnection $connection */
        $messageEnvelope = $this->createMessageEnvelope($message);
        /* @var WindowsMessageEnvelope $messageEnvelope */
        $this->sendMessageEnvelope($connection, $messageEnvelope);

        return $messageEnvelope;
    }

    /**
     * @param string $connectionName
     * @param MessageEnvelopeQueue $queue
     *
     * @throws ConnectionHandlerException
     * @throws ConnectionException
     */
    protected function flushQueueItem(string $connectionName, MessageEnvelopeQueue $queue)
    {
        if ($queue->isEmpty()) {
            return;
        }
        $connection = $this->getConnectionHandler()->getConnection($connectionName);
        /* @var WindowsConnection $connection */
        foreach ($queue->getQueue() as $messageEnvelope) {
            /* @var WindowsMessageEnvelope $messageEnvelope */
            $this->sendMessageEnvelope($connection, $messageEnvelope);
        }
        $queue->clear();
    }

    /**
     * @param $connection
     * @param $messageEnvelope
     *
     * @throws ConnectionException
     * @throws ConnectionHandlerException
     */
    private function sendMessageEnvelope(WindowsConnection $connection, WindowsMessageEnvelope $messageEnvelope)
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
