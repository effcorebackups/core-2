<?php

namespace effectivecore {
          abstract class timer_factory {

  static $data = [];

  static function tap($name) {
    static::$data[$name][] = microtime();
  }

  static function get_period($name, $a, $b) {
    return round(static::$data[$name][$b] - static::$data[$name][$a], 6);
  }

}}