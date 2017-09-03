<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\messages_factory as messages;
          abstract class caches_factory {

  static $data = [];

  static function get($name) {
    if (!isset(static::$data[$name])) {
      $file = new file(dir_dynamic.'cache--'.$name.'.php');
      if ($file->is_exist()) {
        $file->insert(true, $name != 'classes_map');
      }
    }
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
    } else {
      messages::add_new(
        'Can not write "cache-'.$name.'.php" to the directory "dynamic"!'.br.
        'Directory "dynamic" should be writable.'.br.
        'System is working slowly at now.', 'warning'
      );
    }
  }

}}