<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effectivecore {
          abstract class storage {

  static protected $data;

  static function init() {
    storage_files::init('storages');
    foreach (storage_files::$data['storages'] as $c_module_id => $c_module_storages) {
      foreach ($c_module_storages as $c_row_id => $c_storage) {
        static::$data[$c_storage->id] = $c_storage;
      }
    }
  }

  static function rebuild() {
    static::$data = [];
    static::init();
  }

  static function get($storage_id) {
    if (static::$data == null) static::init();
    if (static::$data[$storage_id] instanceof different_cache)
        static::$data[$storage_id] =
        static::$data[$storage_id]->get_different_cache();
    return static::$data[$storage_id];
  }

}}