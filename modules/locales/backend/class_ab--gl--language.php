<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effectivecore {
          use \effectivecore\modules\storage\storage as storage;
          abstract class language {

  protected static $data;

  static function init() {
    foreach (storage::get('settings')->select_group('languages') as $languages) {
      foreach ($languages as $c_language) {
        static::$data[$c_language->code] = $c_language;
      }
    }
  }

  static function get($code) {
    if (!static::$data) static::init();
    return static::$data[$code];
  }

}}