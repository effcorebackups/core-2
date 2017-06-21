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
    return preg_replace_callback('/(%%_[a-z0-9_]+)(?:\-'.
                                     '([a-z0-9_]+)|)/S', '\\effectivecore\\token_factory::_replace_callback', $string);
  }

  protected static function _replace_callback($found) {
    $match = isset($found[1]) ? $found[1] : null;
    $arg_1 = isset($found[2]) ? $found[2] : null;
    if ($match && isset(static::$data[$match])) {
      switch (static::$data[$match]->type) {
        case 'code': return call_user_func(static::$data[$match]->handler, $match, $arg_1);
        case 'text': return static::$data[$match]->value;
        case 'translated_text': return translations::get(static::$data[$match]->value);
      }
    } else {
      return '';
    }
  }

}}