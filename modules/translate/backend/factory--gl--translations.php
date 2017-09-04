<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\languages_factory as languages;
          use \effectivecore\modules\storage\storages_factory as storages;
          abstract class translations_factory {

  protected static $data;

  static function init() {
    foreach (storages::get('settings')->select('translate') as $c_module) {
      foreach ($c_module as $lang_code => $c_strings) {
        foreach ($c_strings as $c_original_text => $c_translated_text) {
          static::$data[$lang_code][$c_original_text] = $c_translated_text;
        }
      }
    }
  }

  static function get($string, $args = [], $lang = '') {
    if (!static::$data) static::init();
    $string = isset(static::$data[$lang ?: languages::get_current()][$string]) ?
                    static::$data[$lang ?: languages::get_current()][$string] : $string;
    foreach ($args as $c_key => $c_value) {
      $string = str_replace('%%_'.$c_key, $c_value, $string);
    }
    return $string;
  }

}}