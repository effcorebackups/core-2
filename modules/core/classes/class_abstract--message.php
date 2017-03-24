<?php

namespace effectivecore {
          use \effectivecore\modules\page\page;
          abstract class message {

  static $data = [];

  static function init() {
    if (isset($_SESSION) &&
       !isset($_SESSION['messages'])) {
      $_SESSION['messages'] = [];
      static::$data = &$_SESSION['messages'];
    }
  }

  static function set($message, $group = 'notice') {
    static::$data[$group][] = $message;
  }

  static function render_all() {
    foreach (static::$data as $c_group => $c_messages) {
      $c_render = [];
      foreach ($c_messages as $c_message) {
        $c_render[] = new html('li', [], $c_message);
      }
      page::add_element(
        new html('ul', ['class' => $c_group], $c_render), 'messages'
      );
    }
  }

}}