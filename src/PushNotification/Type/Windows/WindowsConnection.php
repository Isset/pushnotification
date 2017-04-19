<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Type\Windows;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
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
     * @var string
     */
    private $clientId;
    /**
     * @var string
     */
    private $clientSecret;
    /**
     * @var string
     */
    private $accessToken = null;

    /**
     * WindowsConnection constructor.
     *
     * @param string $type
     * @param string $clientId
     * @param string $clientSecret
     * @param bool $default
     * @param null|HandlerStack $handler
     */
    public function __construct(string $type, string $clientId, string $clientSecret, bool $default = false, HandlerStack $handler = null)
    {
        $clientConfig = [
            'headers' => [
                'Content-Type' => 'text/xml',
                'X-WNS-TYPE' => 'wns/toast',
            ],
            'connect_timeout' => 3,
            'timeout' => 5,
        ];

        if ($handler instanceof HandlerStack) {
            $clientConfig['handler'] = $handler;
        }

        $this->client = new Client($clientConfig);
        $this->type = $type;
        $this->default = $default;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    /**
     * @param Message $message
     *
     * @throws ConnectionException
     * @throws ConnectionHandlerException
     * @throws \RuntimeException
     *
     * @return Response
     */
    public function sendAndReceive(Message $message): Response
    {
        $response = new ConnectionResponseImpl();

        try {
            $request = new Request('POST',
                $message->getIdentifier(),
                ['Authorization' => 'Bearer ' . $this->getAccessToken()],
                $message->getMessage()
            );
            $clientResponse = $this->client->send($request);
            $response->setResponse($clientResponse->getBody()->getContents());
        } catch (RequestException $e) {
            $response->setErrorResponse($e);
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

    /**
     * @return string
     */
    private function getAccessToken(): string
    {
        if ($this->accessToken === null) {
            $client = new Client([
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'connect_timeout' => 3,
                'timeout' => 5,
            ]);

            $this->accessToken = json_decode($client->request('POST', 'https://login.live.com/accesstoken.srf', [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'scope' => 'notify.windows.com',
                ],
            ])->getBody()->getContents())->access_token;
        }

        return $this->accessToken;
    }
}
