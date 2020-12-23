<?php

namespace Nanotube\Common\Utility;

final class StopWatch {
    const MILLISECONDS = 0;
    const SECONDS = 1;
    const MINUTES = 2;
    const DEFAULT = self::SECONDS;

    private $start;
    private $end;

    public function __construct() {
        $this->start();
    }

    public function start() {
        $this->start = microtime(true);
        $this->end = microtime(true);
    }

    public function stop() {
        $this->end = microtime(true);
    }

    public function read($in = self::DEFAULT, $precision = 4) {
        if (!is_int($in) || $in < 0 || $in > 2) $in = self::DEFAULT;
        $elapsed = ($this->end <= $this->start ? microtime(true) : $this->end) - $this->start;
        switch ($in) {
            case self::MILLISECONDS:
                return round($elapsed * 1000, $precision);
                break;
            case self::MINUTES:
                return round($elapsed / 60, $precision);
                break;
            default:
                return round($elapsed, $precision);
        }
    }
}