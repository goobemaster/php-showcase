<?php

namespace Nanotube\Auth;

use Nanotube\Common\ServiceInterface as ServiceInterface;
use Nanotube\Common\Data\Model\CaptchaSessionModel as CaptchaSessionModel;
use Ramsey\Uuid\Uuid as Uuid;

final class CaptchaInterface extends ServiceInterface {
    /** @query */
    public function getNewTestImage($params): object {
        if (!property_exists($params, 'ip') || count(get_object_vars($params)) !== 1) {
            return (object) ['error' => true];
        }
        
        $captcha = new CaptchaSessionModel();
        $uuid = Uuid::uuid4();
        $captcha->uuid = $uuid;
        $captcha->ip = $params->ip;
        $imageBlob = $captcha->randomizeAndGetImage();
        $captcha->export();
        $captcha->setExpire(600); // 10 minutes

        return (object) ['uuid' => $uuid, 'blob' => base64_encode($imageBlob)];
    }

    public function verify($uuid, $userSolution, $userIP): bool {
        $captcha = new CaptchaSessionModel();
        $captcha->uuid = $uuid;
        if (!$captcha->import()) return false;
        return $captcha->ip === $userIP && $captcha->solution === $userSolution;
    }
}