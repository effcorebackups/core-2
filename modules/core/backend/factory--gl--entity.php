<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\modules\storage\storage_factory as storages;
          abstract class entity_factory {

  static $data;

  static function init() {
    foreach (storages::get('settings')->select('entities') as $c_entities) {
      foreach ($c_entities as $c_entity) {
        static::$data[$c_entity->name] = $c_entity;
      }
    }
  }

  static function get($name) {
    if (!static::$data) static::init();
    return static::$data[$name];
  }

}}