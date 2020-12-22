<?php

namespace Nanotube\Auth;

use Nanotube\Common\WebService as WebService;
use Nanotube\Auth\UserInterface as UserInterface;
use Nanotube\Auth\CaptchaInterface as CaptchaInterface;
use Nanotube\Common\Data\Model\UserAccountModel as UserAccountModel;
use Nanotube\Common\Data\Model\UserEmailTakenModel as UserEmailTakenModel;

final class AuthService extends WebService {
    /** @var Nanotube\Auth\UserInterface */
    public $userInterface;

    /** @var Nanotube\Auth\CaptchaInterface */
    public $captchaInterface;

    /** @command */
    public function registerUserIfNotTaken($params): bool {
        // Captcha
        if (!$this->captchaInterface->verify($params->captchaId, $params->captcha, $params->ip)) {
            return false;
        }

        // Is username unique?
        $user = new UserAccountModel();
        $user->uuid = '*';
        $user->screenName = $params->username;
        if ($user->exists()) return false;

        // Is email unique?
        $emailList = new UserEmailTakenModel();
        if ($emailList->valueInList($params->email)) return false;

        // OK
        return $this->userInterface->addUser($params);
    }
}