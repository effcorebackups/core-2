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

  static function render() {
    $rendered = [];
    foreach (static::get_all() as $c_type => $c_messages) {
      foreach ($c_messages as $c_message) $rendered[$c_type][] = $c_message->render();
      $rendered[$c_type] = (new template('message_group', [
        'class'    => $c_type,
        'messages' => implode('', $rendered[$c_type]),
      ]))->render();
      static::del_grp($c_type);
    }
    if (count($rendered)) {
      return (new template('messages', [
        'message_groups' => implode('', $rendered),
      ]))->render();
    }
  }

}}