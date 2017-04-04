<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Type\Android\Message;

use IssetBV\PushNotification\Core\Message\Message;

/**
 * @see https://firebase.google.com/docs/cloud-messaging/send-message.
 */
class AndroidMessage implements Message
{
    /**
     * @var array
     */
    private $payload;

    /**
     * AndroidMessage constructor.
     *
     * @param string $identifier
     */
    public function __construct(string $identifier)
    {
        $this->payload = [
            'to' => $identifier,
        ];
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->payload['to'];
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
    public function getMessage(): array
    {
        return $this->payload;
    }
}
