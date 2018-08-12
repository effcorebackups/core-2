<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effcore {
          abstract class token {

  static protected $cache;

  static function init() {
    foreach (storage::get('files')->select('tokens') as $c_module_id => $c_tokens) {
      foreach ($c_tokens as $c_row_id => $c_token) {
        if (isset(static::$cache[$c_row_id])) console::log_about_duplicate_add('token', $c_row_id);
        static::$cache[$c_row_id] = $c_token;
        static::$cache[$c_row_id]->module_id = $c_module_id;
      }
    }
  }

  static function get($row_id) {
    if (!static::$cache) static::init();
    return isset(static::$cache[$row_id]) ?
                 static::$cache[$row_id] : null;
  }

  static function replace($string) {
    return preg_replace_callback('%(?<prefix>\\%\\%_)'.
                                  '(?<name>[a-z0-9_]+)'.
                                  '(?<args>\\{[a-z0-9_,]+\\}|)%S', function($c_match) {
      $name = !empty($c_match['name']) ? $c_match['name'] : null;
      $args = !empty($c_match['args']) ? explode(',', substr($c_match['args'], 1, -1)) : [];
      $info = static::get($name);
      if ($info) {
        switch ($info->type) {
          case 'code': return call_user_func($info->handler, $name, $args);
          case 'text': return $info->value;
          case 'translated_text': return translation::get($info->value);
        }
      } else {
        return '';
      }
    }, $string);
  }

}}