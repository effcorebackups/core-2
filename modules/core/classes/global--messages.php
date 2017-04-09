<?php

namespace effectivecore {
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
    $rendered = [];
    foreach (static::$data as $c_type => $c_messages) {
      foreach ($c_messages as $c_message) $rendered[$c_type][] = $c_message->render();
      $rendered[$c_type] = (new template('message_group', [
        'class'    => $c_type,
        'messages' => implode('', $rendered[$c_type]),
      ]))->render();
      unset(static::$data[$c_type]);
    }
    if (count($rendered)) {
      return (new template('messages', [
        'message_groups' => implode('', $rendered),
      ]))->render();
    }
  }

}}