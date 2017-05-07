<?php

namespace effectivecore {
          use \effectivecore\settings_factory as settings;
          class entity_instance {

  public $entity;
  public $values;

  function __construct($npath = '', $values = []) {
    $this->values = $values;
    if ($npath) {
      $this->entity = new linker($npath);
    }
  }

  function get_name() {
    return $this->entity->get()->name;
  }

  function get_fields() {
    return array_keys((array)$this->entity->get()->fields);
  }

  function get_ids() {
    return $this->entity->get()->get_ids();
  }

  function get_values($is_ids_only = false) {
    if ($is_ids_only) {
      $values = [];
      foreach ($this->get_ids() as $c_id) {
        $values[$c_id] = $this->values[$c_id];
      }
      return $values;
    } else {
      return $this->values;
    }
  }

  function set_value($name, $value) {
    $this->values[$name] = $value;
  }
  
  function select() {
  }

  function insert() {
  }

  function update() {
  }

  function delete() {
  }

}}