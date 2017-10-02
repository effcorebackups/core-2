<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\messages_factory as messages;
          abstract class dynamic_factory {

  static $type = 'data';
  static $directory = dir_dynamic.'data/';
  static $info = [];
  static $data = [];

  static function get_info() {
    return static::$info;
  }

  static function get($name) {
    if (!isset(static::$data[$name])) {
      $file = new file(static::$directory.static::$type.'--'.$name.'.php');
      if ($file->is_exist()) {
        $file->insert();
      }
    }
    return isset(static::$data[$name]) ?
                 static::$data[$name] : null;
  }

  static function set($name, $data, $info = null) {
    static::$data[$name] = $data;
    $file = new file(static::$directory.static::$type.'--'.$name.'.php');
    if ($info) static::$info[$name] = $info;
    if (is_writable($file->get_dirs_full()) && ((
        is_writable($file->get_path_full()) && $file->is_exist()) ||
                    $file->is_exist() == false)) {
      $file->set_data(
        "<?php".nl.nl."namespace effectivecore { # ".static::$type." for ".$name.nl.nl.($info ?
           factory::data_export($info, '  '.factory::class_get_short_name(static::class).'::$info[\''.$name.'\']') : '').
           factory::data_export($data, '  '.factory::class_get_short_name(static::class).'::$data[\''.$name.'\']').nl.
        "}");
      $file->save();
      if (function_exists('opcache_invalidate')) {
        opcache_invalidate($file->get_path_full());
      }
      return true;
    } else {
      messages::add_new(
        'Can not write file "'.$file->get_file_full().'" to the directory "'.$file->get_dirs_relative().'"!'.br.
        'The system cannot save dynamic file and will work slowly!'.br.
        (!is_writable($file->get_dirs_full()) ? 'Directory "'.$file->get_dirs_relative().'" should be writable!'.br : '').
        (!is_writable($file->get_path_full()) && $file->is_exist() ? 'File "'.$file->get_file_full().'" should be writable!' : ''), 'warning'
      );
    }
  }

}}