<?php

namespace effectivecore {
          abstract class message_factory {

  protected static $data = [];

  static function init() {
    if (isset($_SESSION)) {
      if (!isset($_SESSION['messages'])) {
        $_SESSION['messages'] = [];
      }
      static::$data = &$_SESSION['messages'];
    }
  }

  static function get_all() {
    if (!static::$data) static::init();
    return static::$data;
  }

  static function del_grp($group) {
    if (!static::$data) static::init();
    unset(static::$data[$group]);
  }

  static function add_new($message, $type = 'notice') {
    if (!static::$data) static::init();
    static::$data[$type][] = new message($message, $type);
  }

}}