<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effectivecore\modules\storage {
          use const \effectivecore\br;
          use \effectivecore\timer as timer;
          use \effectivecore\console as console;
          abstract class events_storage {

  static function on_storage_init_before($storage) {
    timer::tap('storage init');
  }

  static function on_storage_init_after($storage) {
    timer::tap('storage init');
    console::add_log(
      'storage',
      'init.',
      'storage %%_id was initialized',
      'ok', timer::get_period('storage init', -1, -2), ['id' => $storage->id.'|storage_pdo']
    );
  }

  static function on_query_before($storage, $query) {
    timer::tap('storage query: '.$storage->query_to_string($query));
  }

  static function on_query_after($storage, $query, $result, $errors) {
    $s_query = 'query = "'.str_replace([' ,', '( ', ' )'], [',', '(', ')'], $storage->query_to_string($query)).'"';
    $s_query_args = count($storage->args) ? br.'args = [\''.implode('\', \'', $storage->args).'\']' : '';
    $s_query_args_beautiful = wordwrap($s_query_args, 50, ' ', true);
    timer::tap('storage query: '.$storage->query_to_string($query));
    console::add_log('storage', 'query',
      $s_query.
      $s_query_args_beautiful,
      $errors[0] == '00000' ? 'ok' : 'error',
      timer::get_period('storage query: '.$storage->query_to_string($query), -1, -2)
    );
  }

}}