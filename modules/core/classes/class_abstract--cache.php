<?php

namespace effectivecore {
          abstract class cache {

  static $data = [];

  static function get($name) {
    if (!isset(static::$data[$name])) {
      $file = new file(dir_cache.'/'.$name.'.php');
      if ($file->is_exist()) {
        $file->insert();
      }
    }
    return isset(static::$data[$name]) ?
                 static::$data[$name] : null;
  }

  static function set($name, $data) {
    $file = new file(dir_cache.'/'.$name.'.php');
    $file->content = "<?php \n\nnamespace effectivecore { # cache for ".$name."\n\n".
                       factory::data_export($data, '  cache::$data[\''.$name.'\']').
                     "\n}";
    return $file->save();
  }

}}