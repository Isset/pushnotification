<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Type\Apple;

use IssetBV\PushNotification\Core\Connection\ConnectionHandlerException;
use IssetBV\PushNotification\Core\Connection\ConnectionHandlerExceptionImpl;
use IssetBV\PushNotification\LoggerTrait;
use LogicException;

/**
 * Class StreamSocket.
 */
class StreamSocket
{
    use LoggerTrait;
    /**
     * Minimum interval to wait for a response.
     */
    const READ_TIMEOUT = 1000000;
    /**
     * @var resource|null
     */
    private $connection;
    /**
     * @var string
     */
    private $certificateFileLocation;
    /**
     * @var string|null
     */
    private $certificatePassPhrase;
    /**
     * @var string
     */
    private $url;

    /**
     * StreamSocket constructor.
     *
     * @param string $url
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }

    /**
     * @param string $certificateFileLocation
     * @param string|null $certificatePassPhrase
     */
    public function setCertificate(string $certificateFileLocation, string $certificatePassPhrase = null)
    {
        $this->certificateFileLocation = $certificateFileLocation;
        $this->certificatePassPhrase = $certificatePassPhrase;
    }

    /**
     * @throws ConnectionHandlerException
     */
    public function connect()
    {
        if (is_resource($this->connection)) {
            return;
        }
        $streamContext = stream_context_create();
        if ($this->certificateFileLocation !== null) {
            stream_context_set_option($streamContext, 'ssl', 'local_cert', $this->certificateFileLocation);

            if ($this->certificatePassPhrase !== null) {
                stream_context_set_option($streamContext, 'ssl', 'passphrase', $this->certificatePassPhrase);
            }
        }
        $this->getLogger()->debug('opening connection to ' . $this->url . ' and  key file: ' . $this->certificateFileLocation . ' and using passphrase: ' . ($this->certificatePassPhrase ? 'yes' : 'no'));

        $connection = @stream_socket_client(
            $this->url,
            $errorCode,
            $errorString,
            (float) ini_get('default_socket_timeout'),
            STREAM_CLIENT_CONNECT,
            $streamContext
        );
        if ($connection === false) {
            throw new ConnectionHandlerExceptionImpl('Failed to connect to  with error #' . $errorCode . ' "' . $errorString . '".');
        }

        stream_set_blocking($connection, false);
        stream_set_write_buffer($connection, 0);
        $this->connection = $connection;
    }

    /**
     * @throws ConnectionHandlerException
     */
    public function reconnect()
    {
        $this->disconnect();
        $this->connect();
    }

    /**
     * @throws LogicException
     *
     * @return string|null
     */
    public function read()
    {
        if (!$this->connected()) {
            throw new LogicException('No connection');
        }

        $streams = [$this->connection];
        $write = $except = null;
        $streamsReadyToRead = stream_select($streams, $write, $except, 0, self::READ_TIMEOUT);
        if ($streamsReadyToRead > 0) {
            return stream_get_contents($this->connection);
        }
    }

    /**
     * @return bool
     */
    public function connected(): bool
    {
        return is_resource($this->connection);
    }

    /**
     * @param string $data
     *
     * @return int
     */
    public function write(string $data): int
    {
        if (!$this->connected()) {
            throw new LogicException('Not connected');
        }

        return (int) fwrite($this->connection, $data, strlen($data));
    }

    /**
     * Close the connection.
     */
    public function disconnect()
    {
        if ($this->connected()) {
            fclose($this->connection);
            $this->connection = null;
        }
    }
}
