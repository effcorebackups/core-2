<?php

namespace effectivecore\modules\storage {
          use \effectivecore\settings_factory as settings;
          abstract class storage_factory {

  static $data;

  static function init() {
    foreach (settings::get('storages') as $c_storages) {
      foreach ($c_storages as $c_storage) {
        static::$data[$c_storage->id] = $c_storage;
      }
    }
  }

  static function get_instance($storage_id) {
    return static::$data[$storage_id];
  }

}}