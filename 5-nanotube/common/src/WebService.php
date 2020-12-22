<?php

namespace Nanotube\Common;

use Nanotube\Common\Utility\Reflectable as Reflectable;
use Nanotube\Common\Utility\ReflectionUtil as ReflectionUtil;
use Nanotube\Common\ServiceInterface as ServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Factory\AppFactory as AppFactory;

abstract class WebService {
    use Reflectable;

    const AUTH = 9010;
    const CONNECTION = 9020;
    const CONTENT = 9030;

    /** @var Slim\App */
    protected $api;

    public function __construct() {
        if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/') return;
        $this->attachInterfaces();
        $this->addServiceRoutes();
        $this->api->run();
    }

    protected function attachInterfaces(): void {
        $this->api = AppFactory::create();
        $this->api->addErrorMiddleware(true, true, true);

        foreach (self::getRefClass()->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            try {
                $ifaceClass = new \ReflectionClass(ReflectionUtil::getPropertyType($property));
                if ($ifaceClass->getParentClass()->getShortName() !== 'ServiceInterface') continue;
                $interface = $ifaceClass->newInstance();
                $serviceName = $property->getName();
                $this->{$serviceName} = $interface;
                $this->addInterfaceRoutes($serviceName, $interface);
            } catch (Exception $e) {
                continue;
            }
        }
    }

    protected function addInterfaceRoutes(&$servicePropName, $iface): void {
        $ifaceName = $iface->getCanonicalName();
        $service = &$this;

        foreach ($iface->contract[ServiceInterface::COMMAND_ANNOTATION] as $ifaceCommand) {
            $ifaceCommandName = $ifaceCommand->getName();
            $route = '/' . $ifaceName . '/' . $ifaceCommandName;

            $this->api->post($route,
                function (Request $request, Response $response) use ($servicePropName, $ifaceCommandName, $service) {
                    $requestBodyJson = json_decode($request->getBody());
                    if ($requestBodyJson === null) {
                        $response = $response->withStatus(400);
                    } else {
                        $success = $service->{$servicePropName}->{$ifaceCommandName}($requestBodyJson);
                        $response = $response->withStatus($success ? 200 : 400);
                    }
                    return $response;
                }
            );
        }

        foreach ($iface->contract[ServiceInterface::QUERY_ANNOTATION] as $ifaceQuery) {
            $ifaceQueryName = $ifaceQuery->getName();
            $route = '/' . $ifaceName . '/' . $ifaceQueryName;

            $this->api->post($route,
                function (Request $request, Response $response) use ($servicePropName, $ifaceQueryName, $service) {
                    $requestBodyJson = json_decode($request->getBody());
                    if ($requestBodyJson === null || !$request->hasHeader('Query')) {
                        $response = $response->withStatus(400);
                    } else {
                        $result = json_encode($service->{$servicePropName}->{$ifaceQueryName}($requestBodyJson));
                        if ($result === null) {
                            $response = $response = $response->withStatus(400);
                        } else {
                            $response->getBody()->write($result);
                        }
                    }
                    return $response->withHeader('Content-Type', 'application/json');
                }
            );
        }
    }

    protected function addServiceRoutes(): void {
        $service = &$this;

        foreach (self::getRefClass()->getMethods(\ReflectionProperty::IS_PUBLIC) as $method) {
            $isCommand = ReflectionUtil::isMethodAnnotatedWith($method, ServiceInterface::COMMAND_ANNOTATION);
            $isQuery = ReflectionUtil::isMethodAnnotatedWith($method, ServiceInterface::QUERY_ANNOTATION);
            $methodName = $method->getName();
            $route = '/' . $methodName;

            if ($isCommand) {
                $this->api->post($route,
                    function (Request $request, Response $response) use ($methodName, $service) {
                        $requestBodyJson = json_decode($request->getBody());
                        if ($requestBodyJson === null) {
                            $response = $response->withStatus(400);
                        } else {
                            $success = $service->{$methodName}($requestBodyJson);
                            $response = $response->withStatus($success ? 200 : 400);
                        }
                        return $response;
                    }
                );
            } else if ($isQuery) {
                $this->api->post($route,
                    function (Request $request, Response $response) use ($methodName, $service) {
                        $requestBodyJson = json_decode($request->getBody());
                        if ($requestBodyJson === null || !$request->hasHeader('Query')) {
                            $response = $response->withStatus(400);
                        } else {
                            $result = json_encode($service->{$methodName}($requestBodyJson));
                            if ($result === null) {
                                $response = $response->withStatus(400);
                            } else {
                                $response->getBody()->write($result);
                            }
                        }
                        return $response->withHeader('Content-Type', 'application/json');
                    }
                );
            }
        } 
    }

    public static function isKnownService($serviceId): bool {
        return in_array($serviceId, self::getRefClass()->getConstants());
    }

    public static function getServiceName($serviceId) {
        if (!self::isKnownService($serviceId)) return null;
        return strtolower(array_keys(self::getRefClass()->getConstants(), $serviceId)[0]);
    }
}