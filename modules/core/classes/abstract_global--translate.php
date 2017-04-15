<?php

namespace effectivecore {
          abstract class translate {

  static $lang_current = 'ru';
  static $data;

  static function init() {
    foreach (settings::$data['translate'] as $c_module) {
      foreach ($c_module as $lang_code => $c_strings) {
        foreach ($c_strings as $c_original_text => $c_translated_text) {
          static::$data[$lang_code][$c_original_text] = $c_translated_text;
        }
      }
    }
  }

  static function t($string) {
    return isset(static::$data[static::$lang_current][$string]) ?
                 static::$data[static::$lang_current][$string] : $string;
  }

}}