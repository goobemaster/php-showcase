<?php

namespace Mock;

use Mock\Request as Request;
use Mock\Response as Response;

/**
 * This is a simplified controller type interface for the mock server.
 * The job of a controller is to generate a response for a given request.
 * 
 * Optional: The router calls the isApplicable() method to check up
 * with the controller before actually running the the index() method.
 * You may or may not want to do validation there, to ignore the check
 * simply return true.
 */
interface Controller {
    /**
     * Generates a response for the given request.
     *
     * @param Request $request
     * @return Response
     */
    public function index($request): Response;

    /**
     * Chance to signal back to the router whether the request is
     * relevant to and valid for this controller.
     *
     * @param Request $request
     * @return boolean
     */
    public function isApplicable($request): bool;
}