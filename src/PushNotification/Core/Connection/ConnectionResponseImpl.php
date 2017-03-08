<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Core\Connection;

use IssetBV\PushNotification\Core\Response;

/**
 * Class ConnectionResponseImpl.
 */
class ConnectionResponseImpl implements Response
{
    /**
     * @var bool
     */
    private $success = true;
    /**
     * @var mixed
     */
    private $response = null;

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @param bool $success
     */
    public function setSuccess(bool $success)
    {
        $this->success = $success;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param mixed $errorResponse
     */
    public function setErrorResponse($errorResponse)
    {
        $this->setSuccess(false);
        $this->response = $errorResponse;
    }

    /**
     * @param $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }
}
