<?php

namespace Nanotube\Webui;

use Nanotube\Common\WebService as WebService;
use Nanotube\Common\WebServiceClient as WebServiceClient;

final class WebApi extends WebService {
    /** @command */
    public function register($params): bool {
        if (!$this->isValidRegistration($params)) return false;

        $authClient = new WebServiceClient(WebService::AUTH);
        if ($authClient->serviceCommand('registerUserIfNotTaken', $params)) {
            return true;
        } else {
            return false;
        }
    }

    private function isValidRegistration($params) {
        return count(get_object_vars($params)) === 3 && property_exists($params, 'email') &&
            property_exists($params, 'password') && property_exists($params, 'username') &&
            preg_match("/^.+@.+\..{2,}$/", $params->email) === 1 &&
            strlen($params->password) >= 9 &&
            strlen($params->username) > 3 && strlen($params->username) <= 50 &&
            preg_match("/^[^\s]+$/", $params->username) === 1;
    }

    /** @query */
    public function login($params): object {
        return false;
    }    
}