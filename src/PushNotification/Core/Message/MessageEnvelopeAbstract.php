<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Core\Message;

use IssetBV\PushNotification\Core\Response;
use PhpOption\Option;

abstract class MessageEnvelopeAbstract implements MessageEnvelope
{
    /**
     * @var Response
     */
    private $response;
    /**
     * @var string
     */
    private $state = self::PENDING;
    /**
     * @var Message
     */
    private $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * @param string $state
     */
    public function setState(string $state)
    {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @return Option<Response>
     */
    public function getResponse(): Option
    {
        return Option::fromValue($this->response);
    }

    /**
     * @param Response $response
     *
     * @return Response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * @return Message
     */
    public function getMessage(): Message
    {
        return $this->message;
    }
}
