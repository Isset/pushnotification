<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Type\Apple\Message;

/**
 * Class AppleMessageAps.
 */
class AppleMessageAps extends AppleMessage
{
    /**
     * @var AppleMessageApsData
     */
    private $appleMessageAps;
    /**
     * @var array
     */
    private $payload = [];

    /**
     * @return AppleMessageApsData
     */
    public function getAps(): AppleMessageApsData
    {
        if ($this->appleMessageAps === null) {
            $this->appleMessageAps = new AppleMessageApsData();
        }

        return $this->appleMessageAps;
    }

    /**
     * @param AppleMessageApsData $appleMessageAps
     */
    public function setAppleMessageAps(AppleMessageApsData $appleMessageAps)
    {
        $this->appleMessageAps = $appleMessageAps;
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
        $message = $this->payload;
        if ($this->appleMessageAps !== null) {
            $message['aps'] = $this->getAps()->toArray();
        }

        return $message;
    }
}
