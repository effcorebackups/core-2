<?php

namespace effectivecore {
          abstract class token {

  static $data;

  static function init() {
    foreach (settings::$data['tokens'] as $c_tokens) {
      foreach ($c_tokens as $c_token) {
        static::$data[$c_token->match] = $c_token;
      }
    }
  }

  static function replace($string) {
    return preg_replace_callback('/%%_[a-z0-9_]+/s', '\\effectivecore\\token::_replace_callback', $string);
  }

  static protected function _replace_callback($found) {
    $match = isset($found[0]) ? $found[0] : null;
    if ($match && isset(static::$data[$match])) {
      switch (static::$data[$match]->type) {
        case 'code': return call_user_func(static::$data[$match]->handler, $match);
        case 'text': return static::$data[$match]->value;
      }
    } else {
      return '';
    }
  }

}}