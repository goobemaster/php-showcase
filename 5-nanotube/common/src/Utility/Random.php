<?php

namespace Nanotube\Common\Utility;

final class Random {
    const ALPHANUMERIC_CHARACTERS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public static function alphanumeric($length = 10): string {
        $poolLength = strlen(self::ALPHANUMERIC_CHARACTERS);
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= self::ALPHANUMERIC_CHARACTERS[rand(0, $poolLength - 1)];
        }
        return $string;
    }
}