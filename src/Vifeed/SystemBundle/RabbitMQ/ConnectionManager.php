<?php

namespace Vifeed\SystemBundle\RabbitMQ;

use PhpAmqpLib\Connection\AMQPConnection;

/**
 * Class ConnectionManager
 *
 * @package Vifeed\SystemBundle\RabbitMQ
 */
class ConnectionManager
{
    protected $host;
    protected $vhost;
    protected $port;
    protected $user;
    protected $password;
    protected $defaultConnection = null;

    /**
     *
     */
    public function __construct($host, $port, $user, $password, $vhost)
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->vhost = $vhost;
    }

    /**
     *
     */
    public function createConnection()
    {
        return new AMQPConnection($this->host, $this->port, $this->user, $this->password, $this->vhost);
    }

    /**
     *
     */
    public function getDefaultConnection()
    {
        if ($this->defaultConnection === null) {
            $this->defaultConnection = $this->createConnection();
        }

        return $this->defaultConnection;
    }
} 