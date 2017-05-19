<?php

namespace effectivecore {
          use \effectivecore\settings_factory as settings;
          abstract class entity_factory {

  static $data;

  static function init() {
    foreach (settings::get('entities') as $c_entities) {
      foreach ($c_entities as $c_entity) {
        static::$data[$c_entity->name] = $c_entity;
      }
    }
  }

  static function get_entity($name) {
    return static::$data[$name];
  }

}}