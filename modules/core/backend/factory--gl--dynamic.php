<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\timers_factory as timers;
          use \effectivecore\console_factory as console;
          use \effectivecore\messages_factory as messages;
          abstract class dynamic_factory {

  static $type = 'data';
  static $data = [];

  static function get($name) {
    timers::tap('dynamic file get: '.$name);
    if (!isset(static::$data[$name])) {
      $file = new file(dir_dynamic.static::$type.'--'.$name.'.php');
      if ($file->is_exist()) {
        $file->insert();
      }
    }
    timers::tap('dynamic file get: '.$name);
    console::add_log('dynamic file', 'get', $name, 'ok', timers::get_period('dynamic file get: '.$name, -1, -2));
    return isset(static::$data[$name]) ?
                 static::$data[$name] : null;
  }

  static function set($name, $data) {
    static::$data[$name] = $data;
    if (is_writable(dir_dynamic)) {
      $file = new file(dir_dynamic.static::$type.'--'.$name.'.php');
      $file->set_data(
        "<?php\n\nnamespace effectivecore { # ".static::$type." for ".$name."\n\n".
           factory::data_export($data, '  '.factory::class_get_short_name(static::class).'::$data[\''.$name.'\']').
        "\n}");
      $file->save();
      if (function_exists('opcache_invalidate')) {
        opcache_invalidate($file->get_path_full());
      }
    } else {
      messages::add_new(
        'Can not write "'.static::$type.'--'.$name.'.php" to the directory "dynamic"!'.br.
        'Directory "dynamic" should be writable.'.br.
        'System is working slowly at now.', 'warning'
      );
    }
  }

}}