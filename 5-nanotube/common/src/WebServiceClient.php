<?php

namespace Nanotube\Common;

use Nanotube\Common\WebService as WebService;

final class WebServiceClient {
    const GET = 'GET';
    const POST = 'POST';

    /** @var \GuzzleHttp\Client */
    private $client;
    /** @var string */
    private $serviceName;
    /** @var int */
    private $serviceId;
    /** @var string */
    private $baseUrl;

    /**
     * @param int $serviceId
     * @throws Exception
     */
    public function __construct($serviceId) {
        $this->serviceId = $serviceId;
        $this->serviceName = WebService::getServiceName($serviceId);
        if ($this->serviceName === null) {
            throw new Exception("Unknown service: {$serviceId} !");
        }
        $this->baseUrl = "http://localhost:{$this->serviceId}";
        $this->client = new \GuzzleHttp\Client();
    }

    /**
     * @param string $keyword
     * @param object $params
     * @return bool
     */
    public function serviceCommand($keyword, $params): bool {
        $jsonBody = json_encode($params);
        if ($jsonBody === null) return false;
        try {
            $response = $this->client->request(self::POST, "{$this->baseUrl}/{$keyword}", ['body' => $jsonBody]);
            return $response->getStatusCode() === 200;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return false;
        }
    }

    /**
     * @param string $interface
     * @param string $keyword
     * @param object $params
     * @return bool
     */
    public function interfaceCommand($interface, $keyword, $params): bool {
        $jsonBody = json_encode($params);
        if ($jsonBody === null) return false;
        try {
            $response = $this->client->request(self::POST, "{$this->baseUrl}/{$interface}/{$keyword}", ['body' => $jsonBody]);
            return $response->getStatusCode() === 200;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return false;
        }
    }

    /**
     * @param string $keyword
     * @param object $params
     * @return object
     */
    public function serviceQuery($keyword, $params): object {
        $jsonBody = json_encode($params);
        if ($jsonBody === null) return (object) [];
        // Ide is kéne catch valszeg de meg kéne nézni a WebService mit csinál...
        $response = $this->client->request(self::GET, "{$this->baseUrl}/{$keyword}", ['body' => $jsonBody]);
        if ($response->getStatusCode() === 200) {
            $responseBodyJson = json_decode($response->getBody());
            return $responseBodyJson === null ? (object) [] : $responseBodyJson;
        }
        return (object) [];
    }

    /**
     * @param string $interface
     * @param string $keyword
     * @param object $params
     * @return object
     */
    public function interfaceQuery($interface, $keyword, $params): object {
        $jsonBody = json_encode($params);
        if ($jsonBody === null) return (object) [];
        $response = $this->client->request(self::GET, "{$this->baseUrl}/{$interface}/{$keyword}", ['body' => $jsonBody]);
        if ($response->getStatusCode() === 200) {
            $responseBodyJson = json_decode($response->getBody());
            return $responseBodyJson === null ? (object) [] : $responseBodyJson;
        }
        return (object) [];
    }
}