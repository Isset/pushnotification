<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Type\Apple\Message;

use IssetBV\PushNotification\Core\Message\MessageEnvelopeAbstract;

/**
 * Class AppleMessageEnvelope.
 */
class AppleMessageEnvelope extends MessageEnvelopeAbstract
{
    /**
     * AppleMessageEnvelope constructor.
     *
     * @param AppleMessage $message
     */
    public function __construct(AppleMessage $message)
    {
        parent::__construct($message);
    }
}
