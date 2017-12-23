<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effectivecore {
          class instance {

  public $entity_name;
  public $values;

  function __construct($entity_name = '', $values = []) {
    $this->set_entity_name($entity_name);
    $this->set_values($values);
  }

  function __get($name)         {return $this->values[$name];}
  function __set($name, $value) {$this->values[$name] = $value;}

  function set_values($values) {$this->values = $values;}
  function get_values($names = []) {
    if (count($names)) {
      return array_intersect_key($this->values, factory::array_values_map_to_keys($names));
    } else {
      return $this->values;
    }
  }

  function get_entity() {return entity::get($this->entity_name);}
  function set_entity_name($entity_name) {$this->entity_name = $entity_name;}

  function select($custom_ids = []) {
    $storage = storage::get($this->get_entity()->get_storage_id());
    return $storage->select_instance($this, $custom_ids);
  }

  function insert() {
    $storage = storage::get($this->get_entity()->get_storage_id());
    return $storage->insert_instance($this);
  }

  function update() {
    $storage = storage::get($this->get_entity()->get_storage_id());
    return $storage->update_instance($this);
  }

  function delete() {
    $storage = storage::get($this->get_entity()->get_storage_id());
    return $storage->delete_instance($this);
  }

  function render() {
  }

  ######################
  ### static methods ###
  ######################

  static protected $data;
  static protected $data_raw;

  static function init() {
    static::$data_raw = storage::get('files')->select_group('instances');
    foreach (static::$data_raw as $c_module_id => $c_module_instances) {
      foreach ($c_module_instances as $c_row_id => $c_instance) {
        static::$data[$c_row_id] = $c_instance;
      }
    }
  }

  static function get($row_id) {
    if   (!static::$data) static::init();
    return static::$data[$row_id];
  }

  static function get_by_module($name) {
    if   (!static::$data_raw) static::init();
    return static::$data_raw[$name];
  }

}}