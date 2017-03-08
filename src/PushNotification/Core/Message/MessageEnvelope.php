<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Core\Message;

use IssetBV\PushNotification\Core\Response;
use PhpOption\Option;

/**
 * Interface MessageEnvelope.
 */
interface MessageEnvelope
{
    const SUCCESS = 'success';
    const PENDING = 'pending';
    const FAILED = 'failed';
    const PARTIAL_FAILED = 'partial_failed';

    /**
     * @return Message
     */
    public function getMessage(): Message;

    /**
     * @return string
     */
    public function getState(): string;

    /**
     * @param string $state
     */
    public function setState(string $state);

    /**
     * @return Option<Response>
     */
    public function getResponse(): Option;

    /**
     * @param Response $response
     *
     * @return Response
     */
    public function setResponse(Response $response);
}
