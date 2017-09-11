<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\modules\storage\storages_factory as storages;
          abstract class languages_factory {

  protected static $data;
  protected static $current_code;

  static function init() {
    static::$current_code = storages::get('settings')->select('current')['locales']->lang_code;
    foreach (storages::get('settings')->select('languages') as $module_id => $languages) {
      foreach ($languages as $c_language) {
        static::$data[$c_language->code] = $c_language;
      }
    }
  }

  static function get($code = null) {
    if (!static::$data) static::init();
    if (!$code) return static::$data[static::$current_code];
                return static::$data[$code];
  }

  static function set_current($code) {
    static::$current_code = $code;
  }

}}