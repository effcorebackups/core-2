<?php

namespace effectivecore {
          abstract class timer_factory {

  static $data = [];

  static function tap($name) {
    static::$data[$name][] = microtime(true);
  }

  static function get_period($name, $a, $b) {
    $result = abs(static::$data[$name][$b] - static::$data[$name][$a]);
    return number_format($result, 6);
  }

}}