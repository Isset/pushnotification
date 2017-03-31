<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Type\Windows\Message;

use IssetBV\PushNotification\Core\Message\Message;

/**
 * Class WindowsMessage.
 */
class WindowsMessage implements Message
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var array
     */
    private $payload = [];

    /**
     * WindowsMessage constructor.
     *
     * @param string $identifier
     */
    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function payloadContainsKey(string $key): bool
    {
        return array_key_exists($key, $this->payload);
    }

    /**
     * @param string $key
     * @param $value
     */
    public function addToPayload(string $key, $value)
    {
        $this->payload[$key] = $value;
    }

    /**
     * @return array
     */
    public function getMessage()
    {
        return $this->payload;
    }
}
