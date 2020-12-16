<?php

namespace Installer\Callback;

use Installer\BaseCallback as BaseCallback;

final class DoneRedirect extends BaseCallback {
    /**
     * Instructs the client side to redirect to a specific url.
     *
     * @param object $userData
     * @return boolean
     */
    public function process($userData): bool {
        header("Location: index.php?login", TRUE, 301);
        return true;
    }
}