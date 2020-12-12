<?php

namespace Mock;

use Mock\Request as Request;
use Mock\Response as Response;
use Mock\Controller as Controller;

/**
 * The responsibility of the Router is to find a controller that
 * can respond to a particular request. In this implementation, we
 * only care about the request path; if a controller class can be
 * found on the path (relative to Controller subfolder), its going
 * to be the designated request handler.
 */
final class Router {
    /** @var Request */
    private $request;
    /** @var string */
    private $controllerClass;
    /** @var Controller */
    private $controller;

    /**
     * Simply stores the request for later use. At this point
     * the request should have been validated.
     *
     * @param Request $request
     * @return void
     */
    public function setRequest($request): void {
        $this->request = $request;
    }

    /**
     * Checks whether a controller class exists that is associated
     * with the path in the request.
     *
     * @return bool
     */
    public function pathExists(): bool {
        $cleanPath = $this->request->getPath();
        $rootPath = realpath(__DIR__ . '/Controller');
        $path = realpath($rootPath . $cleanPath . '.php');
        if (substr($path, 0, strlen($rootPath)) !== $rootPath) return false; // No jailbreaking!
        if (!is_file($path)) return false;
        $this->controllerClass = 'Mock\\Controller' . str_replace('/', '\\', $cleanPath);
        echo 'Mock\\Controller' . str_replace('/', '\\', $cleanPath) . PHP_EOL;
        return $this->isApplicable();
    }

    /**
     * Creates a new instance of the candidate controller, and checks
     * wheter the controller can respond to the request.
     *
     * @return boolean
     */
    private function isApplicable(): bool {
        $refClass = new \ReflectionClass($this->controllerClass);
        $this->controller = $refClass->newInstance();
        return $this->controller->isApplicable($this->request);
    }

    /**
     * @return Response
     * @throws Exception
     */
    public function runController(): Response {
        return $this->controller->index($this->request);
    }
}