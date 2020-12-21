<?php

namespace Nanotube\Common\Data\Model;

use Nanotube\Common\Data\RedisList as RedisList;

final class UserEmailTakenModel extends RedisList {
    public function getKey(): string {
        return "user.email.taken";
    }
}