<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\translations_factory as translations;
          use \effectivecore\modules\storage\storages_factory as storages;
          abstract class token_factory {

  protected static $data;

  static function init() {
    foreach (storages::get('settings')->select_group('tokens') as $c_tokens) {
      foreach ($c_tokens as $c_token) {
        static::$data[$c_token->match] = $c_token;
      }
    }
  }

  static function replace($string) {
    if (!static::$data) static::init();
    return preg_replace_callback('%(?<name>\\%\\%_[a-z0-9_]+)'.
                                  '(?<args>\\{[a-z0-9_,]+\\}|)%sS', function($matches) {
      $name = !empty($matches['name']) ? $matches['name'] : null;
      $args = !empty($matches['args']) ? explode(',', substr($matches['args'], 1, -1)) : [];
      if ($name && isset(static::$data[$name])) {
        switch (static::$data[$name]->type) {
          case 'code': return call_user_func(static::$data[$name]->handler, $name, $args);
          case 'text': return static::$data[$name]->value;
          case 'translated_text': return translations::get(static::$data[$name]->value);
        }
      } else {
        return '';
      }
    }, $string);
  }

}}