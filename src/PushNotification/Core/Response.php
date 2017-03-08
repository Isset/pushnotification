<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Core;

/**
 * Interface Response.
 */
interface Response
{
    /**
     * @return bool
     */
    public function isSuccess(): bool;

    /**
     * @return mixed
     */
    public function getResponse();
}
