<?php

namespace Nanotube\Common;

use Nanotube\Common\Utility\Reflectable as Reflectable;
use Nanotube\Common\Utility\ReflectionUtil as ReflectionUtil;

abstract class ServiceInterface {
    use Reflectable;

    const COMMAND_ANNOTATION = 'command';
    const QUERY_ANNOTATION = 'query';

    public $contract = [self::COMMAND_ANNOTATION => [], self::QUERY_ANNOTATION => []];

    public function __construct() {
        $refClass = self::getRefClass();
        foreach (self::getRefClass()->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $isCommand = ReflectionUtil::isMethodAnnotatedWith($method, self::COMMAND_ANNOTATION);
            $isQuery = ReflectionUtil::isMethodAnnotatedWith($method, self::QUERY_ANNOTATION);
            if ($isCommand) {
                $this->contract[self::COMMAND_ANNOTATION][] = $method;
            } else if ($isQuery) {
                $this->contract[self::QUERY_ANNOTATION][] = $method;
            }
        }
    }

    public function getCanonicalName() {
        $refClass = self::getRefClass();
        $name = $refClass->getShortName();
        if (substr($name, -strlen('Interface')) === 'Interface') $name = substr($name, 0, -9);
        return strtolower($name);
    }
}