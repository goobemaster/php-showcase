<?php

namespace Installer\Callback;

use Installer\BaseCallback as BaseCallback;

final class Server extends BaseCallback {
    /**
     * @param object $userData
     * @return boolean
     */
    public function process($userData): bool {
        /**
         * To fail a step:
         * 
         * $this->message = 'Error message displayed on the alert window';
         * return false;
         * 
         * Form fields are in: $userData->form
         */
        return true;
    }
}