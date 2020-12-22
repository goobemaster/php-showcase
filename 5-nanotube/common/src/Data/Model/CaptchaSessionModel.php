<?php

namespace Nanotube\Common\Data\Model;

use Nanotube\Common\Data\RedisJsonDocument as RedisJsonDocument;
use Nanotube\Common\Utility\Random as Random;
use Nanotube\Common\Utility\Graphics as Graphics;

final class CaptchaSessionModel extends RedisJsonDocument {
    public $uuid;
    public $ip;
    public $solution;

    public function getKey(): string {
        return 'captcha.session.' . $this->uuid;
    }

    public function serialize(): object {
        return (object) [
            'uuid' => $this->uuid,
            'ip' => $this->ip,
            'solution' => $this->solution
        ];
    }

    public function randomizeAndGetImage(): string {
        $this->solution = Random::alphanumeric(7);
        return Graphics::getCaptchaBadgeBlob($this->solution);
    }
}