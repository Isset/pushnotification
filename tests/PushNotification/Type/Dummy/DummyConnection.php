<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Tests\Type\Dummy;

use IssetBV\PushNotification\Core\Connection\Connection;
use IssetBV\PushNotification\Core\Connection\ConnectionException;
use IssetBV\PushNotification\Core\Connection\ConnectionHandlerException;
use IssetBV\PushNotification\Core\Message\Message;
use IssetBV\PushNotification\Core\Response;
use IssetBV\PushNotification\LoggerTrait;

class DummyConnection implements Connection
{
    use LoggerTrait;

    /**
     * @var bool
     */
    private $default;
    /**
     * @var string
     */
    private $type;

    /**
     * DummyConnection constructor.
     *
     * @param string $type
     * @param bool $default
     */
    public function __construct(string $type, bool $default)
    {
        $this->default = $default;
        $this->type = $type;
    }

    /**
     * @param Message $message
     *
     * @throws ConnectionException
     * @throws ConnectionHandlerException
     *
     * @return Response
     */
    public function sendAndReceive(Message $message): Response
    {
        // TODO: Implement sendAndReceive() method.
    }

    /**
     * Send a message without waiting on response.
     *
     * @param Message $message
     *
     * @throws ConnectionException
     */
    public function send(Message $message)
    {
        // TODO: Implement send() method.
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->default;
    }

    public static function withDefault($type = 'test')
    {
        return new self($type, true);
    }
}
