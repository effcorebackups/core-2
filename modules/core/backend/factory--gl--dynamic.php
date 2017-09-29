<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\messages_factory as messages;
          abstract class dynamic_factory {

  static $type = 'data';
  static $data = [];

  static function get($name) {
    if (!isset(static::$data[$name])) {
      $file = new file(dir_dynamic.static::$type.'--'.$name.'.php');
      if ($file->is_exist()) {
        $file->insert();
      }
    }
    return isset(static::$data[$name]) ?
                 static::$data[$name] : null;
  }

  static function set($name, $data) {
    static::$data[$name] = $data;
    $file = new file(dir_dynamic.static::$type.'--'.$name.'.php');
    if (is_writable(dir_dynamic) && $file->is_writable()) {
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
        'File "'.static::$type.'--'.$name.'.php" should be writable.'.br.
        'System is working slowly at now.', 'warning'
      );
    }
  }

}}