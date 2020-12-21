<?php

namespace Nanotube\Common\Data;

use Nanotube\Common\Utility\Reflectable as Reflectable;
use Nanotube\Common\Data\Connection\RedisConnection as RedisConnection;

abstract class RedisBlob extends RedisDataModel implements \Serializable {
    use Reflectable;

    public $blob;

    public abstract function getKey(): string;
    
    public function serialize(): string {
        return $this->blob;
    }
    
    public function unserialize($rawData): void {
        $this->blob = $rawData;
    }

    public function import(): bool {
        $rawData = RedisConnection::get()->get($this->getKey());
        if ($rawData === false) return false;
        $this->unserialize($rawData);
        return true;
    }

    public function export(): bool {
        $rawData = $this->serialize();
        if ($rawData === null) return false;
        return RedisConnection::get()->set($this->getKey(), $rawData);
    }
}