<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\modules\storage\storage_factory as storage;
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

  function get_entity() {return entity_factory::get($this->entity_name);}
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

}}