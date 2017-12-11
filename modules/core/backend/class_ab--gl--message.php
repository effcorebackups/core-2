<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          abstract class message {

  protected static $data = [];

  static function init() {
  }

  static function select_all() {
    if (!static::$data) static::init();
    return static::$data;
  }

  static function insert($message, $type = 'notice') {
    if (!static::$data) static::init();
    if (!isset(static::$data[$type])) static::$data[$type] = [];
    if (!in_array($message, static::$data[$type])) {
      static::$data[$type][] = $message;
    }
  }

  static function render_all() {
    $groups = [];
    foreach (static::select_all() as $c_type => $c_messages) {
      $c_grpoup = new markup('ul', ['class' => [$c_type => $c_type]]);
      foreach ($c_messages as $c_message) {
        $c_grpoup->child_insert(
          new markup('li', [], $c_message)
        );
      }
      $groups[] = $c_grpoup;
    }
    return count($groups) ? (
      new markup('x-messages', [], $groups)
    )->render() : '';
  }

}}