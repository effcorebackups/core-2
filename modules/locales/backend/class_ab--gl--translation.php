<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effectivecore {
          use \effectivecore\locale as locale;
          use \effectivecore\modules\storage\storage as storage;
          abstract class translation {

  protected static $data;

  static function init() {
    foreach (storage::get('settings')->select_group('translations') as $c_module) {
      foreach ($c_module as $code => $c_strings) {
        foreach ($c_strings as $c_original_text => $c_translated_text) {
          static::$data[$code][$c_original_text] = $c_translated_text;
        }
      }
    }
  }

  static function get($string, $args = [], $code = '') {
    if (!static::$data) static::init();
    $string = isset(static::$data[$code ?: locale::get_settings()->lang_code][$string]) ?
                    static::$data[$code ?: locale::get_settings()->lang_code][$string] : $string;
    foreach ($args as $c_key => $c_value) {
      $string = str_replace('%%_'.$c_key, $c_value, $string);
    }
    return $string;
  }

}}