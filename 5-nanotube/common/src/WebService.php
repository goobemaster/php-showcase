<?php

namespace Nanotube\Common;

use Nanotube\Common\Utility\Reflectable as Reflectable;
use Nanotube\Common\Utility\ReflectionUtil as ReflectionUtil;
use Nanotube\Common\ServiceInterface as ServiceInterface;
use Nanotube\Common\Utility\StopWatch as StopWatch;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Factory\AppFactory as AppFactory;
use Opis\Closure\SerializableClosure as SerializableClosure;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

abstract class WebService implements \Serializable {
    use Reflectable;

    const AUTH = 9010;
    const CONNECTION = 9020;
    const CONTENT = 9030;

    /** @var Slim\App */
    protected $api;
    /** @var array */
    private $routes = [];
    /** @var Monolog\Logger */
    private static $logger;

    public function __construct() {
        if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/') return;
        $this->api = AppFactory::create();
        $this->api->addErrorMiddleware(true, true, true);
        $routesFile = dirname(\Phar::running(false)) . '/routes.ser';
        if (is_file($routesFile)) {
            self::log()->debug('Building routes from cache...');
            $this->unserialize(file_get_contents($routesFile));
            foreach ($this->routes as $route => $closure) {
                $this->api->post($route, $closure->getClosure());
            }
        } else {
            self::log()->debug('Building routes for the first time...');
            $this->attachServiceInterfaces();
            $this->attachServices();
            file_put_contents($routesFile, $this->serialize());
        }
        $this->api->run();
    }

    protected function attachServiceInterfaces(): void {
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

    protected function addInterfaceRoutes(string &$servicePropName, ServiceInterface $iface): void {
        $ifaceName = $iface->getCanonicalName();
        $service = &$this;

        foreach ($iface->contract[ServiceInterface::COMMAND_ANNOTATION] as $ifaceCommand) {
            $ifaceCommandName = $ifaceCommand->getName();
            $route = '/' . $ifaceName . '/' . $ifaceCommandName;

            $closure = new SerializableClosure(function (Request $request, Response $response) use ($servicePropName, $ifaceCommandName, $service) {
                $requestBodyJson = json_decode($request->getBody());
                if ($requestBodyJson === null) {
                    $response = $response->withStatus(400);
                } else {
                    $success = $service->{$servicePropName}->{$ifaceCommandName}($requestBodyJson);
                    $response = $response->withStatus($success ? 200 : 400);
                }
                return $response;
            });
            $this->routes[$route] = $closure;
            $this->api->post($route, $closure->getClosure());
        }

        foreach ($iface->contract[ServiceInterface::QUERY_ANNOTATION] as $ifaceQuery) {
            $ifaceQueryName = $ifaceQuery->getName();
            $route = '/' . $ifaceName . '/' . $ifaceQueryName;

            $closure = new SerializableClosure(function (Request $request, Response $response) use ($servicePropName, $ifaceQueryName, $service) {
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
            });
            $this->routes[$route] = $closure;
            $this->api->post($route, $closure->getClosure());
        }
    }

    protected function attachServices(): void {
        $service = &$this;

        foreach (self::getRefClass()->getMethods(\ReflectionProperty::IS_PUBLIC) as $method) {
            $isCommand = ReflectionUtil::isMethodAnnotatedWith($method, ServiceInterface::COMMAND_ANNOTATION);
            $isQuery = ReflectionUtil::isMethodAnnotatedWith($method, ServiceInterface::QUERY_ANNOTATION);
            $methodName = $method->getName();
            $route = '/' . $methodName;

            if ($isCommand) {
                $closure = new SerializableClosure(function (Request $request, Response $response) use ($methodName, $service) {
                    $requestBodyJson = json_decode($request->getBody());
                    if ($requestBodyJson === null) {
                        $response = $response->withStatus(400);
                    } else {
                        $success = $service->{$methodName}($requestBodyJson);
                        $response = $response->withStatus($success ? 200 : 400);
                    }
                    return $response;
                });
                $this->routes[$route] = $closure;
                $this->api->post($route, $closure->getClosure());
            } else if ($isQuery) {
                $closure = new SerializableClosure(function (Request $request, Response $response) use ($methodName, $service) {
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
                });
                $this->routes[$route] = $closure;
                $this->api->post($route, $closure->getClosure());
            }
        }
    }

    public static function isKnownService(int $serviceId): bool {
        return in_array($serviceId, self::getRefClass()->getConstants());
    }

    public static function getServiceName(int $serviceId): string {
        if (!self::isKnownService($serviceId)) return null;
        return strtolower(array_keys(self::getRefClass()->getConstants(), $serviceId)[0]);
    }

    public static function log(): \Monolog\Logger {
        if (self::$logger === null) {
            $serviceName = self::getRefClass()->getShortName();
            $logPath = dirname(\Phar::running(false)) . '/../../logs';
            if (!is_dir($logPath)) mkdir($logPath);
            $logFile = "{$logPath}/{$serviceName}.log";
            touch($logFile);
            self::$logger = new Logger($serviceName);
            self::$logger->pushHandler(new StreamHandler($logFile, Logger::DEBUG));
        }
        return self::$logger;
    }

    public function serialize(): string {
        return serialize($this->routes);
    }

    public function unserialize($serialized) {
        $this->routes = unserialize($serialized);
    }
}