<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\modules\storage\storages_factory as storages;
          abstract class locales_factory {

  protected static $current;

  static function init() {
    static::$current = storages::get('settings')->select('current')['locales'];
  }

  static function get_current() {
    if (!static::$current) static::init();
    return static::$current;
  }

  static function format_date() {}
  static function format_time() {}
  static function format_datetime() {}

  static function format_persent($number, $precision = 2) {
    return static::format_number(floatval($number), $precision).'%';
  }

  static function format_msecond($number, $precision = 6) {
    return static::format_number(floatval($number), $precision);
  }

  static function format_version($number) {
    return static::format_number(floatval($number), 2);
  }

  static function format_number($number, $precision = 0, $decimal_point = null, $thousands_separator = null) {
    $current = static::get_current();
    return number_format($number, $precision,
      is_null($decimal_point)       ? $current->decimal_point       : $decimal_point,
      is_null($thousands_separator) ? $current->thousands_separator : $thousands_separator
    );
  }

}}