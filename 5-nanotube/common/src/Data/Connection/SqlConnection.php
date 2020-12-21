<?php

namespace Nanotube\Common\Data\Connection;

/**
 * @see https://github.com/phpredis/phpredis
 */
final class SqlConnection {
    private static $instance;
    private $connection;

    private function __construct() {
        $this->connection = mysqli_connect("127.0.0.1:3379", "php", "showcase", "phpshowcase", 3379);
        $this->connection->set_charset('utf8');
    }

    public static function get() {
        if (self::$instance === null) self::$instance = new SqlConnection();
        return self::$instance;
    }
}