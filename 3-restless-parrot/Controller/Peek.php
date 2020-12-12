<?php

namespace Mock\Controller;

use Mock\MockServer as MockServer;
use Mock\Controller as Controller;
use Mock\Request as Request;
use Mock\Response as Response;

/**
 * Lists mock Request-Response pairs that has been registered.
 */
final class Peek implements Controller {
    /**
     * @param Request $request
     * @return Response
     */
    public function index($request): Response {
        $body = json_encode((object) [
            'resources' => MockServer::$resources,
            'version' => Response::SERVER_SIGNATURE,
            'uptime' => (new \DateTime())->diff(MockServer::$bootTime)->format('%s')
        ]);
        $response = Response::getDefaultMockServerRespose();
        $response->setBody($body);
        $response->setHeader('Content-Length', strlen($body));
        return $response;
    }

    /**
     * @param Request $request
     * @return boolean
     */
    public function isApplicable($request): bool {
        $acceptHeader = $request->getHeader(Request::HEADER_ACCEPT_KEY);

        return $request->isMethodOf(Request::GET) &&
            (strpos($acceptHeader, Request::ANY_MIME) !== false ||
            strpos($acceptHeader, Request::JSON_MIME) !== false);
    }
}