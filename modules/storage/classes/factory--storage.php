<?php

namespace effectivecore\modules\storage {
          use \effectivecore\storage_instance_s as settings;
          abstract class storage_factory {

  protected static $data;

  static function init() {
    settings::init();
    foreach (settings::$data['storages'] as $c_storages) {
      foreach ($c_storages as $c_storage) {
        static::$data[$c_storage->id] = $c_storage;
      }
    }
  }

  static function get($storage_id) {
    if (!static::$data) static::init();
    return static::$data[$storage_id];
  }

}}