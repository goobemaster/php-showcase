<?php

namespace Nanotube\Common\Data\Connection;

/**
 * @see https://github.com/phpredis/phpredis
 */
final class RedisConnection {
    private static $instance;
    private $redis;

    private function __construct() {
        $this->redis = new \Redis();
        $this->redis->pconnect('localhost', 6379);
        $this->redis->auth('phpshowcase');
    }

    public static function get() {
        if (self::$instance === null) self::$instance = new RedisConnection();
        return self::$instance->redis;
    }
}