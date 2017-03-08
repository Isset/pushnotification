<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Type\Android\Message;

use IssetBV\PushNotification\Core\Message\MessageEnvelopeAbstract;

/**
 * Class AndroidMessageEnvelope.
 */
class AndroidMessageEnvelope extends MessageEnvelopeAbstract
{
    /**
     * AndroidMessageEnvelope constructor.
     *
     * @param AndroidMessage $message
     */
    public function __construct(AndroidMessage $message)
    {
        parent::__construct($message);
    }
}
