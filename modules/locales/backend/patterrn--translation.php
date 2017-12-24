<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effectivecore {
          class translation
          implements \effectivecore\has_different_cache {

  public $code;
  public $data;

  ######################
  ### static methods ###
  ######################

  static protected $cache;

  static function get_non_different_properties() {
    return ['code' => 'code'];
  }

  static function init() {
    foreach (storage::get('files')->select_group('translations') as $c_module_id => $c_module_translations) {
      foreach ($c_module_translations as $c_row_id => $c_translation) {
        static::$cache[$c_translation->code] = $c_translation;
      }
    }
  }

  static function get($string, $args = [], $code = '') {
    $c_code = $code ?: locale::get_settings()->lang_code;
    if (static::$cache == null) static::init();
    if (static::$cache[$c_code] instanceof different_cache)
        static::$cache[$c_code] =
        static::$cache[$c_code]->get_different_cache();
    $string = isset(static::$cache[$c_code]->data[$string]) ?
                    static::$cache[$c_code]->data[$string] : $string;
    foreach ($args as $c_key => $c_value) {
      $string = str_replace('%%_'.$c_key, $c_value, $string);
    }
    return $string;
  }

}}