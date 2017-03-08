<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Type\Apple;

use Exception;
use IssetBV\PushNotification\Core\Connection\ConnectionException;
use IssetBV\PushNotification\Core\Connection\ConnectionHandlerException;
use IssetBV\PushNotification\Core\Message\Message;
use IssetBV\PushNotification\Core\Message\MessageEnvelope;
use IssetBV\PushNotification\Core\Message\MessageEnvelopeQueue;
use IssetBV\PushNotification\Core\NotifierAbstract;
use IssetBV\PushNotification\Core\Response;
use IssetBV\PushNotification\Type\Apple\Exception\AppleNotifyFailedException;
use IssetBV\PushNotification\Type\Apple\Message\AppleMessage;
use IssetBV\PushNotification\Type\Apple\Message\AppleMessageEnvelope;

/**
 * Class AppleNotifier.
 */
class AppleNotifier extends NotifierAbstract
{
    /**
     * @param Message $message
     *
     * @return bool
     */
    public function handles(Message $message): bool
    {
        return $message instanceof AppleMessage;
    }

    /**
     * @param Message $message
     * @param string|null $connectionName
     *
     * @throws ConnectionHandlerException
     * @throws ConnectionException
     * @throws AppleNotifyFailedException
     *
     * @return MessageEnvelope
     */
    protected function sendMessage(Message $message, string $connectionName = null): MessageEnvelope
    {
        /* @var AppleMessage $message */
        try {
            $envelope = $this->createMessageEnvelope($message);
            $connection = $this->getConnectionHandler()->getConnection($connectionName);
            $response = $connection->sendAndReceive($message);
            $envelope->setResponse($response);
            if ($response->isSuccess()) {
                $envelope->setState(MessageEnvelope::SUCCESS);
            } else {
                $envelope->setState(MessageEnvelope::FAILED);
            }

            return $envelope;
        } catch (Exception $e) {
            $this->getLogger()->error('Exception occurred sending an apple message: ' . $e->getMessage());
            throw new AppleNotifyFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param string $connectionName
     * @param MessageEnvelopeQueue $queue
     *
     * @throws AppleNotifyFailedException
     * @throws ConnectionHandlerException
     * @throws ConnectionException
     */
    protected function flushQueueItem(string $connectionName, MessageEnvelopeQueue $queue)
    {
        if ($queue->isEmpty()) {
            return;
        }

        $connection = $this->getConnectionHandler()->getConnection($connectionName);
        /* @var AppleConnection $connection */
        foreach ($queue->getQueue() as $item) {
            /* @var AppleMessageEnvelope $item */
            $connection->send($item->getMessage());
        }

        $response = $this->getResponseData($connection);
        if ($response->isSuccess()) {
            $queue->traverseWith(function (MessageEnvelope $messageEnvelope) {
                $messageEnvelope->setState(MessageEnvelope::SUCCESS);
            });
            $queue->reset();
        } else {
            $this->handleErrorResponse($connectionName, $queue, $response);
        }
    }

    /**
     * @param Message $message
     *
     * @return MessageEnvelope
     */
    protected function createMessageEnvelope(Message $message): MessageEnvelope
    {
        /* @var AppleMessage $message */
        return new AppleMessageEnvelope($message);
    }

    /**
     * @param string $connectionName
     * @param MessageEnvelopeQueue $queue
     * @param Response $response
     *
     * @throws AppleNotifyFailedException
     * @throws ConnectionException
     * @throws ConnectionHandlerException
     */
    private function handleErrorResponse(string $connectionName, MessageEnvelopeQueue $queue, Response $response)
    {
        $error = $response->getResponse();
        if (!array_key_exists('identifier', $error)) {
            $queue->traverseWith(function (MessageEnvelope $messageEnvelope) {
                $messageEnvelope->setState(MessageEnvelope::FAILED);
            });
            $queue->reset();
            throw new AppleNotifyFailedException('Message gave an error but no response all messages marked as failed');
        }
        // Get all the items that are sent before or are the failed identifier
        $preIdentifierQueue = $queue->split($error['identifier']);
        //remove the failed identifier from the queue so we can fail it
        $failedMessage = $preIdentifierQueue->remove($error['identifier'])->getOrThrow(new AppleNotifyFailedException('Failed identifier not found: ' . $error['identifier']));
        $failedMessage->setState(MessageEnvelope::FAILED);
        $failedMessage->setResponse($response);
        // every message before the failed one are successful
        $preIdentifierQueue->traverseWith(function (MessageEnvelope $messageEnvelope) {
            $messageEnvelope->setState(MessageEnvelope::SUCCESS);
        });
        //reflush the remainder of the queue these messages have to be send again
        $this->flushQueueItem($connectionName, $queue);
    }

    /**
     * @param $connection
     *
     * @throws ConnectionHandlerException
     *
     * @return Response
     */
    private function getResponseData(AppleConnection $connection): Response
    {
        return $connection->getResponseData();
    }
}
