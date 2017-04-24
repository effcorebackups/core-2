<?php

namespace effectivecore\modules\storage {
          use \effectivecore\settings;
          abstract class storage {

  static $data;

  static function init() {
    foreach (settings::$data['storages'] as $c_storages) {
      foreach ($c_storages as $c_storage) {
        static::$data[$c_storage->id] = $c_storage;
      }
    }
  }

}}