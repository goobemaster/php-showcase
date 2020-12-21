<?php

namespace Nanotube\Auth;

use Nanotube\Common\WebService as WebService;
use Nanotube\Auth\UserInterface as UserInterface;
use Nanotube\Common\Data\Model\UserAccountModel as UserAccountModel;
use Nanotube\Common\Data\Model\UserEmailTakenModel as UserEmailTakenModel;

final class AuthService extends WebService {
    /** @var Nanotube\Auth\UserInterface */
    public $userInterface;

    /** @command */
    public function registerUserIfNotTaken($params): bool {
        $user = new UserAccountModel();
        $user->uuid = '*';
        $user->screenName = $params->username;
        if ($user->exists()) return false;

        $emailList = new UserEmailTakenModel();
        if ($emailList->valueInList($params->email)) return false;

        return $this->userInterface->addUser($params);
    }
}