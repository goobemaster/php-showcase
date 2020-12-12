<?php

namespace Mock;

use Mock\Request as Request;

trait ControllerPostTrait {
    abstract protected function _getMethod();

    /**
     * @param Request $request
     * @return Response
     */
    public function index($request): Response {
        $userRequest = $request->getBodyAsJsonObject();
        $mockRequest = Request::fromUserDefinedFields(
            $this->_getMethod(),
            $userRequest->req_path,
            (array) $userRequest->req_headers,
            $userRequest->req_body
        );
        $mockRequestHash = (string) $mockRequest;

        if (!isset(MockServer::$resources[$mockRequestHash])) {
            MockServer::$resources[$mockRequestHash]['response'] = new Response(
                $userRequest->res_code,
                $userRequest->res_body,
                (array) $userRequest->res_headers                                
            );
            MockServer::$resources[$mockRequestHash]['request'] = $mockRequest;
        }
       
        $body = json_encode((object) [
            'message' => 'ok',
            'hash' => $mockRequestHash
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
        echo var_export($request, true) . PHP_EOL;

        $acceptHeader = $request->getHeader(Request::HEADER_ACCEPT_KEY);
        $body = $request->getBodyAsJsonObject();

        return $request->isMethodOf(Request::POST) &&
            $body !== (object) [] &&
            property_exists($body, 'req_headers') &&
            is_object($body->req_headers) &&
            property_exists($body, 'req_body') &&
            is_string($body->req_body) &&
            property_exists($body, 'req_path') &&
            is_string($body->req_path) &&
            property_exists($body, 'res_code') &&
            is_int($body->res_code) &&
            property_exists($body, 'res_headers') &&
            is_object($body->res_headers) &&
            property_exists($body, 'res_body') &&
            is_string($body->res_body) &&            
            (strpos($acceptHeader, Request::JSON_MIME) !== false ||
            strpos($acceptHeader, Request::ANY_MIME) !== false);
    }
}