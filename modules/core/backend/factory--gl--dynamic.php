<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\messages_factory as messages;
          abstract class dynamic_factory {

  static $type = 'data';
  static $info = [];
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
       ($file->is_exist() == false ||
       ($file->is_exist() && is_writable($file->get_path_full())))) {
      $info = ['created' => date(format_datetime, time())];
      $file->set_data(
        "<?php\n\nnamespace effectivecore { # ".static::$type." for ".$name.nl.nl.
           factory::data_export($info, '  '.factory::class_get_short_name(static::class).'::$info[\''.$name.'\']').
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
        (!is_writable($file->get_path_full()) && $file->is_exist() ? 'File "'.$file->get_file_full().'" should be writable!' : ''), 'warning'
      );
    }
  }

}}