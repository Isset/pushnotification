<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\src\PushNotification\Type\Windows\Message;

use SimpleXMLElement;

/**
 * Class WindowsMessageElement.
 */
class WindowsMessageElement
{
    /**
     * @var SimpleXMLElement
     */
    private $element;

    /**
     * WindowsMessageElement constructor.
     *
     * @param SimpleXMLElement $element
     * @param array $attributes
     */
    public function __construct(SimpleXMLElement $element, array $attributes = [])
    {
        $this->element = $element;
        foreach ($attributes as $attribute => $value) {
            $this->addAttribute($attribute, $value);
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     *
     * @return WindowsMessageElement
     */
    public function addElement(string $key, $value = null, array $attributes = []): WindowsMessageElement
    {
        return new self($this->element->addChild($key, $value), $attributes);
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->element->asXML();
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function addAttribute(string $key, string $value)
    {
        $this->element->addAttribute($key, $value);
    }
}
