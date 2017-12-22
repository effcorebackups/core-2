<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effectivecore {
          abstract class storage {

  static protected $data;

  static function init() {
    storage_files::init('storages');
    foreach (storage_files::$data['storages'] as $c_storages) {
      foreach ($c_storages as $c_storage) {
        static::$data[$c_storage->id] = $c_storage;
      }
    }
  }

  static function rebuild() {
    static::$data = [];
    static::init();
  }

  static function get($storage_id) {
    if   (!static::$data) static::init();
    return static::$data[$storage_id];
  }

}}