<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Tests\Type\Dummy;

use IssetBV\PushNotification\Core\Message\Message;

final class DummyMessage implements Message
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $message;

    /**
     * DummyMessage constructor.
     *
     * @param string $identifier
     * @param string $message
     */
    public function __construct(string $identifier, string $message)
    {
        $this->identifier = $identifier;
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    public static function simple()
    {
        return new self('we', 'dont care');
    }
}
