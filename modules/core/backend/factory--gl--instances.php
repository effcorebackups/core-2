<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\modules\storage\storages_factory as storages;
          abstract class instances_factory {

  protected static $data;
  protected static $data_raw;

  static function init() {
    static::$data_raw = storages::get('settings')->select_group('instances');
    foreach (static::$data_raw as $c_instances) {
      foreach ($c_instances as $row_id => $c_instance) {
        static::$data[$row_id] = $c_instance;
      }
    }
  }

  static function get($row_id) {
    if (!static::$data) static::init();
    return static::$data[$row_id];
  }

  static function get_by_module($name) {
    if (!static::$data_raw) static::init();
    return static::$data_raw[$name];
  }

}}