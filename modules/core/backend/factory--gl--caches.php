<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\timers_factory as timers;
          use \effectivecore\console_factory as console;
          use \effectivecore\messages_factory as messages;
          abstract class caches_factory {

  static $data = [];

  static function get($name) {
    timers::tap('cache get: '.$name);
    if (!isset(static::$data[$name])) {
      $file_name = dir_dynamic.'cache--'.$name.'.php';
      $file = new file($file_name);
      if ($file->is_exist()) {
        $file->insert();
      }
    }
    timers::tap('cache get: '.$name);
    console::add_log('cache', 'get', $name, 'ok', timers::get_period('cache get: '.$name, -1, -2));
    return isset(static::$data[$name]) ?
                 static::$data[$name] : null;
  }

  static function set($name, $data) {
    static::$data[$name] = $data;
    if (is_writable(dir_dynamic)) {
      $file = new file(dir_dynamic.'cache--'.$name.'.php');
      $file->set_data(
        "<?php\n\nnamespace effectivecore { # cache for ".$name."\n\n".
           factory::data_export($data, '  caches_factory::$data[\''.$name.'\']').
        "\n}");
      $file->save();
      if (function_exists('opcache_invalidate')) {
        opcache_invalidate($file->get_path_full());
      }
    } else {
      messages::add_new(
        'Can not write "cache-'.$name.'.php" to the directory "dynamic"!'.br.
        'Directory "dynamic" should be writable.'.br.
        'System is working slowly at now.', 'warning'
      );
    }
  }

}}