<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\modules\storage\storage_factory as storage;
          abstract class entity_factory {

  protected static $data;
  protected static $data_raw;

  static function init() {
    static::$data_raw = storage::get('settings')->select_group('entities');
    foreach (static::$data_raw as $c_entities) {
      foreach ($c_entities as $c_entity) {
        static::$data[$c_entity->name] = $c_entity;
      }
    }
  }

  static function get($name) {
    if (!static::$data) static::init();
    return static::$data[$name];
  }

  static function get_all_by_module($name) {
    if (!static::$data_raw) static::init();
    return static::$data_raw[$name];
  }

}}