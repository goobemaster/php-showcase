<?php

namespace Mock\Controller;

use Mock\MockServer as MockServer;
use Mock\Controller as Controller;
use Mock\Request as Request;
use Mock\Response as Response;

/**
 * Removes a registered mock request identified by its hash.
 */
final class Remove implements Controller {
    /**
     * @param Request $request
     * @return Response
     */
    public function index($request): Response {
        $mockRequestHash = $request->getBodyAsJsonObject()->req_hash;

        if (isset(MockServer::$resources[$mockRequestHash])) {
            unset(MockServer::$resources[$mockRequestHash]);
        }
       
        $body = json_encode((object) ['message' => 'ok']);
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
        $body = $request->getBodyAsJsonObject();

        return $request->isMethodOf(Request::POST) &&
            $body !== (object) [] &&
            property_exists($body, 'req_hash') &&
            is_string($body->req_hash) &&          
            (strpos($acceptHeader, Request::ANY_MIME) !== false ||
            strpos($acceptHeader, Request::JSON_MIME) !== false);
    }
}