<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\modules\storage\storages_factory as storages;
          abstract class languages_factory {

  protected static $data;
  protected static $current;

  static function init() {
    static::$current = storages::get('settings')->select('languages')['translate']->current;
    foreach (storages::get('settings')->select('languages') as $module_id => $languages) {
      foreach ($languages->available as $c_lang) {
        static::$data[$c_lang->code] = $c_lang;
      }
    }
  }

  static function get($lang) {
    if (!static::$data) static::init();
    return isset(static::$data[$lang]) ?
                 static::$data[$lang] : null;
  }

  static function get_current() {
    if (!static::$current) static::init();
    return static::$current;
  }

  static function set_current($lang) {
    static::$current = $lang;
  }

}}