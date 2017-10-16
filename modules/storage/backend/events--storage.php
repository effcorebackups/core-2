<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\storage {
          use \effectivecore\timers_factory as timers;
          use \effectivecore\console_factory as console;
          use \effectivecore\translations_factory as translations;
          abstract class events_storage {

  static function on_storage_init_before($instance) {
    timers::tap('storage init');
  }

  static function on_storage_init_after($instance) {
    timers::tap('storage init');
    console::add_log(
      'storage', 'init.', 'storage "%%_name" was initialized', 'ok', timers::get_period('storage init', 0, 1), ['name' => $instance->id]
    );
  }

  static function on_query_before($instance, $query) {
    timers::tap('storage query: '.$query);
  }

  static function on_query_after($instance, $query, $result, $errors) {
    timers::tap('storage query: '.$query);
    console::add_log(
      'storage', 'query', $query, $errors[0] == '00000' ? 'ok' : 'error', timers::get_period('storage query: '.$query, -1, -2)
    );
  }

}}