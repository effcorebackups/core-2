<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\storage {
          use \effectivecore\factory as factory;
          use \effectivecore\timer_factory as timer;
          use \effectivecore\console_factory as console;
          abstract class events_storage {

  static function on_storage_init_before($storage) {
    timer::tap('storage init');
  }

  static function on_storage_init_after($storage) {
    timer::tap('storage init');
    console::add_log(
      'storage', 'init.', 'storage %%_id was initialized', 'ok', timer::get_period('storage init', 0, 1), ['id' => $storage->id]
    );
  }

  static function on_query_before($storage, $query) {
    timer::tap('storage query: '.$storage->query_to_string($query));
  }

  static function on_query_after($storage, $query, $result, $errors) {
    $s_query = $storage->query_to_string($query);
    $s_query_beautiful = wordwrap(str_replace([' ,', '( ', ' )'], [',', '(', ')'], $s_query), 50, ' ', true);
    timer::tap('storage query: '.$s_query);
    console::add_log(
      'storage', 'query', $s_query_beautiful, $errors[0] == '00000' ? 'ok' : 'error', timer::get_period('storage query: '.$s_query, -1, -2)
    );
  }

}}