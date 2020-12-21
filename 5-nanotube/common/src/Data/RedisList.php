<?php

namespace Nanotube\Common\Data;

use Nanotube\Common\Utility\Reflectable as Reflectable;
use Nanotube\Common\Data\Connection\RedisConnection as RedisConnection;

abstract class RedisList extends RedisDataModel implements \Countable {
    use Reflectable;

    public $items = [];
    public $uuid;

    public abstract function getKey(): string;

    public function count(): int {
        $count = RedisConnection::get()->lLen($this->getKey());
        return is_int($count) ? $count : 0;
    }

    public function importAll(): bool {
        if ($this->count() === 0) return false;
        $this->items = RedisConnection::get()->lRange($this->getKey(), 0, -1);
        return true;
    }

    public function exportAll(): bool {
        if (!is_array($this->items)) return false;
        $redis = RedisConnection::get();
        if (empty($this->items)) {
            $this->delete();
            return true;
        }
        $key = $this->getKey();
        $itemCount = count($this->items);
        $lastIndex = $this->count() - 1;
        $index = 0;
        foreach ($this->items as $item) {
            $success = $index <= $lastIndex ? $redis->lSet($key, $index, $this->items[$index]) : $redis->rPush($key, $index, $this->items[$index]);
            if (!$success) return false;
            $index++;
        }
        return $lastIndex < $itemCount - 1 ? true : $redis->lTrim($key, 0, $itemCount - 1);
    }

    public function valueInList($value) {
        $this->importAll();
        return in_array($value, $this->items);        
    }

    public function push($value) {
        $this->items[] = $value;
    }
}