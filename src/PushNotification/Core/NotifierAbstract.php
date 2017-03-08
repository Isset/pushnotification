<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Core;

use IssetBV\PushNotification\Core\Connection\ConnectionException;
use IssetBV\PushNotification\Core\Connection\ConnectionHandler;
use IssetBV\PushNotification\Core\Connection\ConnectionHandlerException;
use IssetBV\PushNotification\Core\Exception\NotifyFailedException;
use IssetBV\PushNotification\Core\Message\Message;
use IssetBV\PushNotification\Core\Message\MessageEnvelope;
use IssetBV\PushNotification\Core\Message\MessageEnvelopeQueue;
use IssetBV\PushNotification\Core\Message\MessageEnvelopeQueueImpl;
use IssetBV\PushNotification\LoggerTrait;
use LogicException;
use Psr\Log\LoggerInterface;

/**
 * Class NotifierAbstract.
 */
abstract class NotifierAbstract implements Notifier
{
    use LoggerTrait {
        setLogger as setLoggerTrait;
    }

    /**
     * @var MessageEnvelopeQueue[]
     */
    protected $queues = [];

    /**
     * @var ConnectionHandler
     */
    private $connectionHandler;

    /**
     * AppleNotifier constructor.
     *
     * @param ConnectionHandler $connectionHandler
     */
    public function __construct(ConnectionHandler $connectionHandler)
    {
        $this->connectionHandler = $connectionHandler;
    }

    /**
     * @return ConnectionHandler
     */
    public function getConnectionHandler(): ConnectionHandler
    {
        return $this->connectionHandler;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->setLoggerTrait($logger);
        $this->connectionHandler->setLogger($logger);
    }

    /**
     * @param Message $message
     * @param string $connectionName
     *
     * @throws LogicException
     *
     * @return MessageEnvelope
     */
    public function send(Message $message, string $connectionName = null): MessageEnvelope
    {
        if (!$this->handles($message)) {
            throw new LogicException('Message couldn\'t be handled by this notifier');
        }

        return $this->sendMessage($message, $connectionName);
    }

    /**
     * @param Message $message
     * @param string $connectionName
     *
     * @throws ConnectionHandlerException
     * @throws LogicException
     *
     * @return MessageEnvelope
     */
    public function queue(Message $message, string $connectionName = null): MessageEnvelope
    {
        if (!$this->handles($message)) {
            throw new LogicException('Message couldn\'t be handled by this notifier');
        }

        return $this->addToQueue($message, $connectionName);
    }

    /**
     * Flushes the queue to the notifier queues.
     *
     * @param string $connectionName
     *
     * @throws NotifyFailedException
     * @throws ConnectionException
     * @throws ConnectionHandlerException
     */
    public function flushQueue(string $connectionName = null)
    {
        if (empty($this->queues)) {
            return;
        }

        if ($connectionName === null) {
            foreach ($this->queues as $queueConnectionName => $queue) {
                $this->flushQueueItem($queueConnectionName, $queue);
            }
        } elseif (array_key_exists($connectionName, $this->queues)) {
            $this->flushQueueItem($connectionName, $this->queues[$connectionName]);
        }
    }

    /**
     * @param Message $message
     * @param string|null $connectionName
     *
     * @throws ConnectionHandlerException
     *
     * @return MessageEnvelope
     */
    protected function addToQueue(Message $message, string $connectionName = null): MessageEnvelope
    {
        $envelope = $this->createMessageEnvelope($message);
        if ($connectionName === null) {
            $connectionName = $this->connectionHandler->getDefaultConnection()->getType();
        }
        if (!array_key_exists($connectionName, $this->queues)) {
            $this->queues[$connectionName] = new MessageEnvelopeQueueImpl();
        }
        $this->queues[$connectionName]->add($envelope);

        return $envelope;
    }

    /**
     * @param Message $message
     *
     * @return MessageEnvelope
     */
    abstract protected function createMessageEnvelope(Message $message): MessageEnvelope;

    /**
     * @param Message $message
     * @param string $connectionName
     *
     * @return MessageEnvelope
     */
    abstract protected function sendMessage(Message $message, string $connectionName = null): MessageEnvelope;

    /**
     * @param string $connectionName
     * @param MessageEnvelopeQueue $queue
     *
     * @throws NotifyFailedException
     * @throws ConnectionHandlerException
     * @throws ConnectionException
     */
    abstract protected function flushQueueItem(string $connectionName, MessageEnvelopeQueue $queue);
}
