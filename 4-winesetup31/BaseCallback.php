<?php

namespace Installer;

/**
 * Server side callback you can assign to a setup step.
 */
abstract class BaseCallback {
    /**
     * This error message will be displayed on an alert modal in
     * the browser in case you return false in process().
     * That means the user cannot proceed to the next step until
     * he/she corrects the errors.
     *
     * @var string
     */
    public $message = '';

    /**
     * You can carry out whatever task needed on the server in this
     * method.
     * 
     * All user data (or the contents of the window) can be
     * queried from the $userData parameter. Return true if all
     * went fine, and you wish to enable the user to carry on with
     * the next step.
     * 
     * You may decide to carry out some tasks you immediately
     * can, but still flag the execution a failure. Return false
     * in this case. Don't forget to set $message.
     *
     * @param array $userData
     * @return boolean
     */
    public function process($userData): bool {
        return true;
    }
}