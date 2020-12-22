<?php

namespace Nanotube\Common\Data;

use Nanotube\Common\Data\Connection\RedisConnection as RedisConnection;

abstract class RedisDataModel {
    public abstract function getKey(): string;

    public function reset(): void {
        foreach (self::getRefClass()->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isDefault()) continue;
            $property->setValue($property->hasDefaultValue() ? $property->getDefaultValue() : null);
        }
    }

    public function delete(): bool {
        return RedisConnection::get()->del($this->getKey()) === 1;
    }

    public function exists(): bool {
        return RedisConnection::get()->exists($this->getKey()) === 1;
    }

    public function setExpire($seconds): bool {
        return RedisConnection::get()->expire($this->getKey(), (int) $seconds);
    }
}