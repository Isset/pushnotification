<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Type\Windows;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use IssetBV\PushNotification\Core\Connection\Connection;
use IssetBV\PushNotification\Core\Connection\ConnectionException;
use IssetBV\PushNotification\Core\Connection\ConnectionHandlerException;
use IssetBV\PushNotification\Core\Connection\ConnectionResponseImpl;
use IssetBV\PushNotification\Core\Message\Message;
use IssetBV\PushNotification\Core\Response;
use IssetBV\PushNotification\LoggerTrait;

/**
 * Class WindowsConnection.
 */
class WindowsConnection implements Connection
{
    use LoggerTrait;

    /**
     * @var string
     */
    private $type;
    /**
     * @var bool
     */
    private $default;
    /**
     * @var Client
     */
    private $client;

    /**
     * WindowsConnection constructor.
     *
     * @param string $type
     * @param bool $default
     */
    public function __construct(string $type, bool $default = false)
    {
        $this->type = $type;
        $this->default = $default;
        $this->client = new Client([
            'headers' => [
                'Content-Type: text/xml',
                'X-WindowsPhone-Target: toast',
                'X-NotificationClass: 2',
            ],
            'connect_timeout' => 3,
            'timeout' => 5,
        ]);
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
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><wp:Notification xmlns:wp="WPNotification" />');
        $toast = $xml->addChild('wp:Toast');
        foreach ($message->getMessage() as $element => $value) {
            $toast->addChild($element, htmlspecialchars($value, ENT_XML1 | ENT_QUOTES));
        }
        $response = new ConnectionResponseImpl();

        $request = new Request('POST', $message->getIdentifier(), [], $xml->asXML());
        try {
            $clientResponse = $this->client->send($request);
            $response->setResponse($clientResponse->getBody()->getContents());
        } catch (RequestException $e) {
            $response->setErrorResponse($e->getMessage());
        }

        return $response;
    }

    /**
     * Send a message without waiting on response.
     *
     * @param Message $message
     *
     * @throws ConnectionException
     * @throws \IssetBV\PushNotification\Core\Connection\ConnectionHandlerException
     */
    public function send(Message $message)
    {
        $this->sendAndReceive($message);
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
}
