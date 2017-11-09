<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

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
    if (!isset(static::$data[$type])) static::$data[$type] = [];
    if (!in_array($message, static::$data[$type])) {
      static::$data[$type][] = $message;
    }
  }

  static function render_all() {
    $groups = [];
    foreach (static::get_all() as $c_type => $c_messages) {
      $c_grpoup = new markup('ul', ['class' => [$c_type => $c_type]]);
      foreach ($c_messages as $c_message) {
        $c_grpoup->child_insert(
          new markup('li', [], $c_message)
        );
      }
      static::del_grp($c_type);
      $groups[] = $c_grpoup;
    }
    return count($groups) ? (
      new markup('x-messages', [], $groups)
    )->render() : '';
  }

}}