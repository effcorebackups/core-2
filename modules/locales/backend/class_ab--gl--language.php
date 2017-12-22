<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effectivecore {
          abstract class language {

  static protected $data;

  static function init() {
    foreach (storage::get('files')->select_group('languages') as $languages) {
      foreach ($languages as $c_language) {
        static::$data[$c_language->code] = $c_language;
      }
    }
  }

  static function get($code) {
    if   (!static::$data) static::init();
    return static::$data[$code];
  }

}}