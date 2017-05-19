<?php

namespace effectivecore {
          use \effectivecore\settings_factory as settings;
          abstract class translate_factory {

  protected static $lang_current = 'ru';
  protected static $data;

  static function init() {
    foreach (settings::get('translate') as $c_module) {
      foreach ($c_module as $lang_code => $c_strings) {
        foreach ($c_strings as $c_original_text => $c_translated_text) {
          static::$data[$lang_code][$c_original_text] = $c_translated_text;
        }
      }
    }
  }

  static function get($string, $lang = '') {
    if (!static::$data) static::init();
    return isset(static::$data[$lang ?: static::$lang_current][$string]) ?
                 static::$data[$lang ?: static::$lang_current][$string] : $string;
  }

}}