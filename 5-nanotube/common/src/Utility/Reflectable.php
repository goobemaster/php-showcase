<?php

namespace Nanotube\Common\Utility;

trait Reflectable {
    protected static $refClass = [];

    public static function getRefClass(): \ReflectionClass {
        $staticName = get_called_class();
        if (!array_key_exists($staticName, self::$refClass)) self::$refClass[$staticName] = new \ReflectionClass(get_called_class());
        return self::$refClass[$staticName];
    }
}