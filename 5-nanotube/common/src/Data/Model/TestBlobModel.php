<?php

namespace Nanotube\Common\Data\Model;

use Nanotube\Common\Data\RedisBlob as RedisBlob;

final class TestBlobModel extends RedisBlob {
    public $uuid;

    public function getKey(): string {
        return "test.blob.{$this->uuid}";
    }
}