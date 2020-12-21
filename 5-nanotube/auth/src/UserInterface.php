<?php

namespace Nanotube\Auth;

use Nanotube\Common\ServiceInterface as ServiceInterface;
use Nanotube\Common\Data\Model\UserAccountModel as UserAccountModel;
use Nanotube\Common\Data\Model\UserEmailTakenModel as UserEmailTakenModel;
use Ramsey\Uuid\Uuid as Uuid;

final class UserInterface extends ServiceInterface {
    /** @command */
    public function addUser($params): bool {
        $user = new UserAccountModel();
        $user->uuid = Uuid::uuid4();
        $user->email = $params->email;
        $user->screenName = $params->username;
        $user->passwordHash = password_hash($params->password, PASSWORD_DEFAULT);
        $user->exportAll();

        $emailList = new UserEmailTakenModel();
        $emailList->importAll();
        $emailList->push($params->email);
        $emailList->exportAll();

        return true;
    }

    /** @query */
    public function verifyPassword($params): object {
        return (object) ['test' => 'ok'];
    }
}