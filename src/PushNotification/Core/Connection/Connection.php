<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Core\Connection;

use IssetBV\PushNotification\Core\Message\Message;
use IssetBV\PushNotification\Core\Response;
use Psr\Log\LoggerAwareInterface;

/**
 * Interface Connection.
 */
interface Connection extends LoggerAwareInterface
{
    /**
     * @param Message $message
     *
     * @throws ConnectionException
     * @throws ConnectionHandlerException
     *
     * @return Response
     */
    public function sendAndReceive(Message $message): Response;

    /**
     * Send a message without waiting on response.
     *
     * @param Message $message
     *
     * @throws ConnectionException
     */
    public function send(Message $message);

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @return bool
     */
    public function isDefault(): bool;
}
