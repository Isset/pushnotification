<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Type\Windows\Message;

use IssetBV\PushNotification\Core\Message\MessageEnvelopeAbstract;

/**
 * Class WindowsMessageEnvelope.
 */
class WindowsMessageEnvelope extends MessageEnvelopeAbstract
{
    /**
     * WindowsMessageEnvelope constructor.
     *
     * @param WindowsMessage $message
     */
    public function __construct(WindowsMessage $message)
    {
        parent::__construct($message);
    }
}
