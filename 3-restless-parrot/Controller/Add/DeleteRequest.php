<?php

namespace Mock\Controller\Add;

use Mock\Controller as Controller;
use Mock\Request as Request;
use Mock\Response as Response;
use Mock\ControllerPostTrait as ControllerPostTrait;
use Mock\MockServer as MockServer;

/**
 * Registers a new DELETE mock request.
 */
final class DeleteRequest implements Controller {
    use ControllerPostTrait;

    protected function _getMethod() {
        return Request::METHOD['delete'];
    }
}