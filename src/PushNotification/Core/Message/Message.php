<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Core\Message;

/**
 * Interface Message.
 */
interface Message
{
    /**
     * @return mixed
     */
    public function getIdentifier();

    /**
     * @return mixed
     */
    public function getMessage();
}
