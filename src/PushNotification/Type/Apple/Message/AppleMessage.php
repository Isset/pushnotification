<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Type\Apple\Message;

use DateTime;
use IssetBV\PushNotification\Core\Message\Message;

abstract class AppleMessage implements Message
{
    /**
     * Internal counter for apple messages so they have a unique identifier in this session.
     *
     * @var int
     */
    private static $identifierCounter = 1;

    /** @var string */
    private $deviceToken;

    /** @var int */
    private $expiresAt = 0;

    /** @var int */
    private $identifier;

    /**
     * AppleMessage constructor.
     *
     * @param string $deviceIdentifier
     */
    public function __construct(string $deviceIdentifier)
    {
        $this->identifier = self::$identifierCounter++;
        $this->deviceToken = $deviceIdentifier;
    }

    /**
     * @return int
     */
    public function getIdentifier(): int
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getDeviceToken(): string
    {
        return $this->deviceToken;
    }

    /**
     * @return int
     */
    public function getExpiresAt(): int
    {
        return $this->expiresAt;
    }

    /**
     * @param DateTime $expiresAt
     */
    public function setExpiresAt(DateTime $expiresAt)
    {
        $this->expiresAt = (int) $expiresAt->format('U');
    }

    abstract public function getMessage(): array;
}
