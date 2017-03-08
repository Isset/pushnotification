<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Core\Connection;

use IssetBV\PushNotification\LoggerTrait;
use Psr\Log\LoggerInterface;

/**
 * Class ConnectionHandler.
 */
class ConnectionHandlerImpl implements ConnectionHandler
{
    use LoggerTrait {
        setLogger as traitSetLogger;
    }

    /**
     * @var Connection[]
     */
    private $connections = [];

    /**
     * @var Connection
     */
    private $defaultConnection;

    /**
     * @param string $type
     *
     * @throws ConnectionHandlerExceptionImpl
     *
     * @return Connection
     */
    public function getConnection(string $type = null): Connection
    {
        if ($type === null) {
            return $this->getDefaultConnection();
        }
        if (!$this->hasConnectionType($type)) {
            throw new ConnectionHandlerExceptionImpl('connection not found for type: ' . $type);
        }

        return $this->connections[$type];
    }

    /**
     * @throws ConnectionHandlerExceptionImpl
     *
     * @return Connection
     */
    public function getDefaultConnection(): Connection
    {
        if ($this->defaultConnection === null) {
            throw new ConnectionHandlerExceptionImpl('No default connection found');
        }

        return $this->defaultConnection;
    }

    /**
     * @param string $type
     *
     * @throws ConnectionHandlerExceptionImpl
     */
    public function setDefaultConnectionByType(string $type)
    {
        $this->defaultConnection = $this->getConnection($type);
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->traitSetLogger($logger);
        foreach ($this->connections as $connection) {
            $connection->setLogger($logger);
        }
    }

    /**
     * @param Connection $connection
     * @param bool $useLogger
     *
     * @throws ConnectionHandlerExceptionImpl
     */
    public function addConnection(Connection $connection, bool $useLogger = true)
    {
        if ($useLogger) {
            $connection->setLogger($this->getLogger());
        }
        $this->connections[$connection->getType()] = $connection;
        if ($connection->isDefault()) {
            $this->setDefaultConnectionByType($connection->getType());
        }
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function hasConnectionType(string $type): bool
    {
        return array_key_exists($type, $this->connections);
    }
}
