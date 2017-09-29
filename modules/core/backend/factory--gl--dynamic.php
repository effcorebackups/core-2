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
    if (is_writable(dir_dynamic) &&
        is_writable($file->get_path_full())) {
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
        'Can not write "'.$file->get_file_full().'" to the directory "dynamic"!'.br.
        'System is working slowly or / and cannot save dynamic changes!'.br.
        (!is_writable(dir_dynamic) ? 'Directory "dynamic" should be writable!'.br : '').
        (!is_writable($file->get_path_full()) ? 'File "'.$file->get_file_full().'" should be writable!' : ''), 'warning'
      );
    }
  }

}}