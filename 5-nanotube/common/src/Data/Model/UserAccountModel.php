<?php

namespace Nanotube\Common\Data\Model;

use Nanotube\Common\Data\RedisHashMap as RedisHashMap;

final class UserAccountModel extends RedisHashMap {
    public $uuid;
    public $email;
    public $screenName;
    public $passwordHash;

    public function getKey(): string {
        return "user.account.{$this->uuid}.{$this->screenName}";
    }
}