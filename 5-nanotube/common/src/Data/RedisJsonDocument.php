<?php

namespace Nanotube\Common\Data;

use Nanotube\Common\Utility\Reflectable as Reflectable;
use Nanotube\Common\Data\Connection\RedisConnection as RedisConnection;

abstract class RedisJsonDocument extends RedisDataModel implements \Serializable {
    use Reflectable;

    public abstract function getKey(): string;
    
    public abstract function serialize(): object;
    
    public function unserialize($object): void {
        foreach (self::getRefClass()->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $name = $property->getName();
            if (!property_exists($object, $name)) continue;
            $property->setValue($this, $object->{$name});
        }
    }

    public function import(): bool {
        $jsonString = RedisConnection::get()->get($this->getKey());
        if ($jsonString === false) return false;
        $jsonObject = json_decode($jsonString);
        if ($jsonObject === null) return false;
        $this->unserialize($jsonObject);
        return true;
    }

    public function export(): bool {
        $jsonObject = json_encode($this->serialize());
        if ($jsonObject === null) return false;
        return RedisConnection::get()->set($this->getKey(), $jsonObject);
    }
}