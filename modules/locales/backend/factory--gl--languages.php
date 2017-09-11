<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\modules\storage\storages_factory as storages;
          abstract class languages_factory {

  protected static $data;

  static function init() {
    foreach (storages::get('settings')->select('languages') as $languages) {
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