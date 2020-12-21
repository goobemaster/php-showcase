<?php

namespace Nanotube\Common\Data;

use Nanotube\Common\Utility\Reflectable as Reflectable;
use Nanotube\Common\Data\Connection\RedisConnection as RedisConnection;

abstract class RedisHashMap extends RedisDataModel {
    use Reflectable;

    public abstract function getKey(): string;
    
    public function import($hashKey): bool {
        $refClass = self::getRefClass();
        if (!$refClass->hasProperty($hashKey) || !$refClass->getProperty($hashKey)->isPublic()) return false;
        return $this->{$hashKey} = RedisConnection::get()->hGet($this->getKey(), $hashKey);
    }

    public function export($hashKey): bool {
        $refClass = self::getRefClass();
        if (!$refClass->hasProperty($hashKey) || !$refClass->getProperty($hashKey)->isPublic()) return false;
        return $this->{$hashKey} = RedisConnection::get()->hDel($this->getKey(), $hashKey);
    }

    public function importAll(): void {
        $redis = RedisConnection::get();
        $key = $this->getKey();
        foreach (self::getRefClass()->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $property->setValue($this, $redis->hGet($key, $property->getName()));
        }
    }

    public function exportAll(): void {
        $redis = RedisConnection::get();
        $key = $this->getKey();
        foreach (self::getRefClass()->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $redis->hSet($key, $property->getName(), $property->getValue($this));
        }
    }
}