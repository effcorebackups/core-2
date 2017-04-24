<?php

namespace effectivecore {
          abstract class messages_factory {

  static $data = [];

  static function init() {
    if (isset($_SESSION)) {
      if (!isset($_SESSION['messages'])) $_SESSION['messages'] = [];
      static::$data = &$_SESSION['messages'];
    }
  }

  static function add_new($message, $type = 'notice') {
    static::$data[$type][] = new message($message, $type);
  }

}}