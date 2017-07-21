<?php

namespace effectivecore {
          use \effectivecore\translate_factory as translations;
          use \effectivecore\modules\storage\storage_factory as storages;
          abstract class token_factory {

  protected static $data;

  static function init() {
    foreach (storages::get('settings')->select('tokens') as $c_tokens) {
      foreach ($c_tokens as $c_token) {
        static::$data[$c_token->match] = $c_token;
      }
    }
  }

  static function replace($string) {
    if (!static::$data) static::init();
    return preg_replace_callback('%(?<name>\\%\\%_[a-z0-9_]+)'.
                                  '(?<args>\\{[a-z0-9_,]+\\}|)%sS', function($matches) {
      $name = isset($matches['name']) ? $matches['name'] : null;
      $args = isset($matches['args']) ? array_filter(explode(',', substr($matches['args'], 1, -1))) : [];
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