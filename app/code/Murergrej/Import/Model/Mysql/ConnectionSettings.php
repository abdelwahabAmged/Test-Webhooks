<?php

namespace Murergrej\Import\Model\Mysql;

use Laminas\Config\Config;

class ConnectionSettings extends Config
{
    const KEY_HOST = 'host';
    const KEY_PORT = 'port';
    const KEY_DBNAME = 'dbname';
    const KEY_USER = 'username';
    const KEY_PASSWORD = 'password';
    const KEY_INIT_STATEMENTS = 'initStatements';

    public function __construct(array $array = [], $allowModifications = false)
    {
        parent::__construct($array, $allowModifications);
        $this->setInitStatements('SET NAMES utf8');
    }

    public function setHost($host)
    {
        $this->__set(self::KEY_HOST, $host);
    }

    public function setPort($port)
    {
        $this->__set(self::KEY_PORT, $port);
    }

    public function setDbname($dbname)
    {
        $this->__set(self::KEY_DBNAME, $dbname);
    }

    public function setUser($user)
    {
        $this->__set(self::KEY_USER, $user);
    }

    public function setPassword($password)
    {
        $this->__set(self::KEY_PASSWORD, $password);
    }

    public function setInitStatements($initStatements)
    {
        $this->__set(self::KEY_INIT_STATEMENTS, $initStatements);
    }

    public function getHost()
    {
        return $this->__get(self::KEY_HOST);
    }

    public function getDbname()
    {
        return $this->__get(self::KEY_DBNAME);
    }

    public function getPort()
    {
        return $this->__get(self::KEY_PORT);
    }

    public function getUser()
    {
        return $this->__get(self::KEY_USER);
    }

    public function getPassword()
    {
        return $this->__get(self::KEY_PASSWORD);
    }

    public function getInitStatements()
    {
        return $this->__get(self::KEY_INIT_STATEMENTS);
    }
}
