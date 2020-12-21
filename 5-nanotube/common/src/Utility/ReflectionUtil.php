<?php

namespace Nanotube\Common\Utility;

final class ReflectionUtil {
    public static function getPropertyType($property): string {
        preg_match('/\@var (.+)\s/', $property->getDocComment(), $matches);
        if (!isset($matches[1])) return null;
        return $matches[1];
    }

    public static function isMethodAnnotatedWith($method, $annotationName): bool {
        return preg_match("/\@{$annotationName}\s/", $method->getDocComment()) === 1;
    }
}