<?php

namespace Mock\Controller\Add;

use Mock\Controller as Controller;
use Mock\Request as Request;
use Mock\Response as Response;
use Mock\ControllerPostTrait as ControllerPostTrait;

/**
 * Registers a new GET mock request.
 */
final class GetRequest implements Controller {
    use ControllerPostTrait;

    protected function _getMethod() {
        return Request::METHOD['get'];
    }
}