<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Type\Windows\Message;

use IssetBV\PushNotification\Core\Message\Message;
use SimpleXMLElement;

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
     * @var WindowsMessageElement
     */
    private $base;

    /**
     * WindowsMessage constructor.
     *
     * @param string $identifier
     */
    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
        $this->base = new WindowsMessageElement(new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><toast/>'));
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
     * @return WindowsMessageElement
     */
    public function addElement(string $key): WindowsMessageElement
    {
        return $this->base->addElement($key);
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->base->toString();
    }
}
