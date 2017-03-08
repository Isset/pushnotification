<?php

declare(strict_types=1);

namespace IssetBV\PushNotification\Core\Connection;

use Psr\Log\LoggerAwareInterface;

/**
 * Class ConnectionHandler.
 */
interface ConnectionHandler extends LoggerAwareInterface
{
    /**
     * @param string $type
     *
     * @throws ConnectionHandlerException
     *
     * @return Connection
     */
    public function getConnection(string $type = null): Connection;

    /**
     * @throws ConnectionHandlerException
     *
     * @return Connection
     */
    public function getDefaultConnection(): Connection;

    /**
     * @param string $type
     *
     * @throws ConnectionHandlerException
     */
    public function setDefaultConnectionByType(string $type);

    /**
     * @param Connection $connection
     * @param bool $useLogger
     *
     * @throws ConnectionHandlerException
     */
    public function addConnection(Connection $connection, bool $useLogger = true);

    /**
     * @param string $type
     *
     * @return bool
     */
    public function hasConnectionType(string $type): bool;
}
