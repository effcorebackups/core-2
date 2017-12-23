<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effectivecore {
          abstract class translation {

  static protected $data;

  static function init() {
    foreach (storage::get('files')->select_group('translations') as $c_module_id => $c_module_translations) {
      foreach ($c_module_translations as $c_row_id => $c_strings) {
        foreach ($c_strings as $c_original_text => $c_translated_text) {
          static::$data[$c_row_id][$c_original_text] = $c_translated_text;
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