<?php

declare(strict_types=1);

namespace IssetBV\PushNotification;

use IssetBV\PushNotification\Core\Exception\NotifyFailedException;
use IssetBV\PushNotification\Core\Message\Message;
use IssetBV\PushNotification\Core\Message\MessageEnvelope;
use IssetBV\PushNotification\Core\Notifier;
use LogicException;

/**
 * Class NotificationCenter.
 */
class NotificationCenter implements Notifier
{
    use LoggerTrait;

    /**
     * @var Notifier[]
     */
    private $notifiers = [];

    /**
     * @param Message $message
     * @param string $connectionName
     *
     * @throws NotifierNotFoundException
     * @throws NotifyFailedException
     *
     * @return MessageEnvelope
     */
    public function send(Message $message, string $connectionName = null): MessageEnvelope
    {
        return $this->getNotifierForMessage($message)->send($message, $connectionName);
    }

    /**
     * @param Message $message
     * @param string|null $connectionName
     *
     * @throws NotifierNotFoundException
     *
     * @return MessageEnvelope
     */
    public function queue(Message $message, string $connectionName = null): MessageEnvelope
    {
        return $this->getNotifierForMessage($message)->queue($message, $connectionName);
    }

    /**
     * Flushes the queue to the notifier queues.
     *
     * @param string $connectionName
     */
    public function flushQueue(string $connectionName = null)
    {
        foreach ($this->notifiers as $notifier) {
            $notifier->flushQueue($connectionName);
        }
    }

    /**
     * @param Message $message
     *
     * @return bool
     */
    public function handles(Message $message): bool
    {
        try {
            $this->getNotifierForMessage($message);

            return true;
        } catch (NotifierNotFoundException $e) {
            return false;
        }
    }

    /**
     * Add a notifier to the Notification center.
     *
     * @param Notifier $notifier
     * @param bool $setLogger if false the default logger will not be set to the notifier
     *
     * @throws LogicException
     */
    public function addNotifier(Notifier $notifier, bool $setLogger = true)
    {
        if ($notifier instanceof self) {
            throw new LogicException('Cannot add self');
        }

        if ($setLogger) {
            $notifier->setLogger($this->getLogger());
        }
        $this->notifiers[] = $notifier;
    }

    /**
     * @param Message $message
     *
     * @throws NotifierNotFoundException
     *
     * @return Notifier
     */
    public function getNotifierForMessage(Message $message): Notifier
    {
        foreach ($this->notifiers as $notifier) {
            if ($notifier->handles($message)) {
                return $notifier;
            }
        }
        throw new NotifierNotFoundException();
    }
}
