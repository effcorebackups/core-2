<?php

namespace effectivecore {
          use \effectivecore\modules\page\page;
          class messages {

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

# non static declarations

  function render() {
    $r_content = [];
    foreach (static::$data as $c_type => $c_messages) {
      $r_content[] = (new html('ul', ['class' => $c_type], $c_messages))->render();
      unset(static::$data[$c_type]);
    }
    return count($r_content) ? (new html('messages', [], $r_content))->render() : '';
  }

}}