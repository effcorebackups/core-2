<?php

namespace effectivecore {
          class messages {

  function render() {
    $rendered = [];
    foreach (messages_factory::$data as $c_type => $c_messages) {
      foreach ($c_messages as $c_message) $rendered[$c_type][] = $c_message->render();
      $rendered[$c_type] = (new template('message_group', [
        'class'    => $c_type,
        'messages' => implode('', $rendered[$c_type]),
      ]))->render();
      unset(messages_factory::$data[$c_type]);
    }
    if (count($rendered)) {
      return (new template('messages', [
        'message_groups' => implode('', $rendered),
      ]))->render();
    }
  }

}}