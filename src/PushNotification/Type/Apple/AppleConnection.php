<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Type\Apple;

use IssetBV\PushNotification\Type\Apple\Message\AppleMessage;
use IssetBV\PushNotification\Core\Connection\Connection;
use IssetBV\PushNotification\Core\Connection\ConnectionException;
use IssetBV\PushNotification\Core\Connection\ConnectionExceptionImpl;
use IssetBV\PushNotification\Core\Connection\ConnectionHandlerException;
use IssetBV\PushNotification\Core\Connection\ConnectionResponseImpl;
use IssetBV\PushNotification\Core\Message\Message;
use IssetBV\PushNotification\Core\Response;
use IssetBV\PushNotification\LoggerTrait;

/**
 * Class AppleConnection.
 */
class AppleConnection implements Connection
{
    use LoggerTrait;

    /**
     * Binary command to send a message to the APS gateway (Internal use).
     */
    const COMMAND = 1;

    /**
     * Binary size of a device token (Internal use).
     */
    const TOKEN_SIZE = 32;

    /**
     * Minimum interval between sending two messages in microseconds.
     */
    const SEND_INTERVAL = 10000;

    /**
     * @var string
     */
    private $type;
    /**
     * @var bool
     */
    private $default;
    /**
     * @var StreamSocket
     */
    private $streamSocket;

    /**
     * Connection constructor.
     *
     * @param string $url
     * @param string $type
     * @param string $pemKeyFile
     * @param string $pemPasswordPhrase
     * @param bool $default
     */
    public function __construct(string $url, string $type, string $pemKeyFile, string $pemPasswordPhrase = null, bool $default = false)
    {
        $this->type = $type;
        $this->default = $default;
        $this->streamSocket = new StreamSocket($url);
        $this->streamSocket->setCertificate($pemKeyFile, $pemPasswordPhrase);
    }

    /**
     * @param Message $message
     *
     * @throws ConnectionException
     * @throws ConnectionHandlerException
     *
     * @return Response
     */
    public function sendAndReceive(Message $message): Response
    {
        $this->send($message);

        return $this->getResponseData();
    }

    /**
     * @throws ConnectionHandlerException
     *
     * @return Response
     */
    public function getResponseData(): Response
    {
        $connectionResponse = new ConnectionResponseImpl();
        $errorResponse = $this->streamSocket->read();
        if ($errorResponse !== null) {
            $this->getLogger()->error('Send error: ' . $errorResponse);
            $this->streamSocket->disconnect();
            $response = @unpack('Ccommand/Cstatus/Nidentifier', $errorResponse);
            if (!empty($response)) {
                $connectionResponse->setErrorResponse($response);
            } else {
                $connectionResponse->setErrorResponse(null);
            }
        }

        return $connectionResponse;
    }

    /**
     * Send a message without waiting on response.
     *
     * @param Message $message
     *
     * @throws ConnectionException
     * @throws ConnectionHandlerException
     */
    public function send(Message $message)
    {
        /* @var AppleMessage $message */
        $buildMessage = $this->buildMessage($message);
        $this->streamSocket->connect();
        $bytesSend = $this->streamSocket->write($buildMessage);
        if (strlen($buildMessage) !== $bytesSend) {
            $this->streamSocket->disconnect();
            throw new ConnectionExceptionImpl();
        }
        usleep(self::SEND_INTERVAL);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->default;
    }

    /**
     * @param AppleMessage $message
     *
     * @return string
     */
    private function buildMessage(AppleMessage $message): string
    {
        $jsonMessage = json_encode($message->getMessage());
        $jsonMessageLength = strlen($jsonMessage);

        $payload =
            pack(
                'CNNnH*n',
                self::COMMAND,
                $message->getIdentifier(),
                $message->getExpiresAt(),
                self::TOKEN_SIZE,
                $message->getDeviceToken(),
                $jsonMessageLength
            );
        $payload .= $jsonMessage;

        return $payload;
    }
}
