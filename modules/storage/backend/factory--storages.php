<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\storage {
          use \effectivecore\storage_settings as settings;
          abstract class storages_factory {

  protected static $data;

  static function init() {
    settings::init('storages');
    foreach (settings::$data['storages'] as $c_storages) {
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
    if (!static::$data) static::init();
    return static::$data[$storage_id];
  }

}}