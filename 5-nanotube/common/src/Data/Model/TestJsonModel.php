<?php

namespace Nanotube\Common\Data\Model;

use Nanotube\Common\Data\RedisJsonDocument as RedisJsonDocument;

final class TestJsonModel extends RedisJsonDocument {
    public $uuid;
    public $name;
    public $age;

    public function getKey(): string {
        return 'test.json.' . $this->uuid;
    }

    public function serialize(): object {
        return (object) [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'age' => $this->age
        ];
    }
}