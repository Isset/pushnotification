<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Tests\Type\Dummy;

use Psr\Log\AbstractLogger;

final class DummyLogger extends AbstractLogger
{
    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = [])
    {
        // TODO: Implement log() method.
    }
}
