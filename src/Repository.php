<?php

namespace MilesPong\Horizon;

use Illuminate\Contracts\Redis\Factory as Redis;

/**
 * Class Repository
 *
 * @package MilesPong\Horizon
 */
class Repository
{
    /**
     * @var \Illuminate\Contracts\Redis\Factory
     */
    protected $redis;

    /**
     * @var
     */
    protected $prefix;

    /**
     * @var
     */
    protected $connection;

    /**
     * Repository constructor.
     *
     * @param \Illuminate\Contracts\Redis\Factory $redis
     * @param string $prefix
     * @param string $connection
     */
    public function __construct(Redis $redis, $prefix = '', $connection = 'default')
    {
        $this->redis = $redis;
        $this->setPrefix($prefix);
        $this->setConnection($connection);
    }

    /**
     * @param $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = !empty($prefix) ? "$prefix:" : '';
    }

    /**
     * @param $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $masterId
     * @param array $plans
     * @return mixed
     */
    public function push(string $masterId, array $plans)
    {
        return $this->connection()->rpush($this->getKey($masterId), [serialize($plans)]);
    }

    /**
     * Note that 'Undefined class' is a known question.
     *
     * @return \Illuminate\Redis\Connections\Connection|\Predis\Client
     */
    public function connection()
    {
        return $this->redis->connection($this->connection);
    }

    /**
     * @param string $masterId
     * @return string
     */
    protected function getKey(string $masterId)
    {
        return $this->prefix . md5($masterId);
    }

    /**
     * @param string $masterId
     * @return mixed
     */
    public function pop(string $masterId)
    {
        return unserialize($this->connection()->lpop($this->getKey($masterId)));
    }
}
