<?php

namespace effectivecore {
          use \effectivecore\modules\page\page;
          abstract class message {

  static $placeholders = [];

  static function set($message, $group = 'notice') {
    if (!isset(static::$placeholders[$group])) {
      static::$placeholders[$group] = new html('ul', ['class' => [$group]]);
      page::add_element(static::$placeholders[$group], 'messages');
    }
    static::$placeholders[$group]->add_element(new html('li', [], $message));
  }

  static function set_before_redirect($message, $group = 'notice') {
    if (isset($_SESSION)) {
      $_SESSION['messages'][$group][] = $message;
    }
  }

  static function show_after_redirect() {
    if (!empty($_SESSION['messages'])) {
    # show all messages which stored in session
      foreach ($_SESSION['messages'] as $c_group => $c_messages) {
        foreach ($c_messages as $c_message) {
          static::set($c_message, $c_group);
        }
      }
    # delete all session messages
      unset($_SESSION['messages']);
    }
  }

}}