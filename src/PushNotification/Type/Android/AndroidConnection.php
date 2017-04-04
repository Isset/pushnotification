<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Type\Android;

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
use IssetBV\PushNotification\Type\Android\Message\AndroidMessage;

/**
 * Class AndroidConnection.
 */
class AndroidConnection implements Connection
{
    use LoggerTrait;

    /**
     * @var Client
     */
    private $client;
    /**
     * @var string
     */
    private $type;
    /**
     * @var bool
     */
    private $default;
    /**
     * @var bool
     */
    private $dryRun;

    /**
     * AndroidConnection constructor.
     *
     * @param string $type
     * @param string $apiUrl
     * @param string $apiKey
     * @param int $timeout
     * @param bool $dryRun
     * @param bool $default
     * @param null|HandlerStack $handler
     */
    public function __construct(string $type, string $apiUrl, string $apiKey, int $timeout, bool $dryRun = false, bool $default = false, HandlerStack $handler = null)
    {
        $clientConfig = [
            'base_uri' => rtrim($apiUrl, '/'),
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'key=' . $apiKey,
            ],
            'timeout' => $timeout,
        ];

        if ($handler instanceof HandlerStack) {
            $clientConfig['handler'] = $handler;
        }

        $this->client = new Client($clientConfig);
        $this->type = $type;
        $this->default = $default;
        $this->dryRun = $dryRun;
    }

    /**
     * @param Message $message
     *
     * @return Response
     */
    public function sendAndReceive(Message $message): Response
    {
        /* @var AndroidMessage $message */
        $data = $message->getMessage();
        if ($this->dryRun) {
            $data['dry_run'] = true;
        }
        $response = new ConnectionResponseImpl();
        try {
            $requestData = json_encode($data);
            $request = new Request('POST', '', [], $requestData);
            $clientResponse = $this->client->send($request);
            $data = json_decode($clientResponse->getBody()->getContents(), true);

            $response->setResponse($data);
            $response->setSuccess($data['success'] > 0);
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
     * @throws ConnectionHandlerException
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
