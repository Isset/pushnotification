<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Tests\Type\Dummy;

use IssetBV\PushNotification\Core\Message\Message;
use IssetBV\PushNotification\Core\Message\MessageEnvelopeAbstract;

final class DummyMessageEnvelope extends MessageEnvelopeAbstract
{
    public static function withSimpleMessage()
    {
        return self::withMessageWithIdentifier('identifier');
    }

    public static function withMessageWithIdentifier($identifier)
    {
        return new self(new DummyMessage($identifier, 'message'));
    }
}
