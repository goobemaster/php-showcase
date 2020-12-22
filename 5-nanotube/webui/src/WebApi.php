<?php

namespace Nanotube\Webui;

use Nanotube\Common\WebService as WebService;
use Nanotube\Common\WebServiceClient as WebServiceClient;
use Nanotube\Common\Utility\Net as Net;

final class WebApi extends WebService {
    /** @command */
    public function register($params): bool {
        if (!$this->isValidRegistration($params)) return false;

        $params->ip = Net::getUserIP();
        $authClient = new WebServiceClient(WebService::AUTH);
        if ($authClient->serviceCommand('registerUserIfNotTaken', $params)) {
            return true;
        } else {
            return false;
        }
    }

    // TODO: Find better place / generalise
    private function isValidRegistration($params) {
        return count(get_object_vars($params)) === 5 && property_exists($params, 'email') &&
            property_exists($params, 'password') && property_exists($params, 'username') &&
            property_exists($params, 'captchaId') && property_exists($params, 'captcha') &&
            preg_match("/^.+@.+\..{2,}$/", $params->email) === 1 &&
            strlen($params->password) >= 9 &&
            strlen($params->username) > 3 && strlen($params->username) <= 50 &&
            preg_match("/^[^\s]+$/", $params->username) === 1;
    }

    /** @query */
    public function login($params): object {
        return false;
    }

    /** @query */
    public function getCaptchaSession($params): object {
        $authClient = new WebServiceClient(WebService::AUTH);
        return $authClient->interfaceQuery('captcha', 'getNewTestImage',
            (object) ['ip' => Net::getUserIP()]
        );
    }    
}