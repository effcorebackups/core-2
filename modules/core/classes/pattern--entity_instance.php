<?php

namespace effectivecore {
          use \effectivecore\settings_factory as settings;
          class entity_instance {

  public $entity;
  public $values;

  function __construct($entity = '', $values = []) {
    $this->entity = $entity;
    $this->values = $values;
  }

  function get_entity() {
    return factory::npath_get_object($this->entity, settings::$data);
  }

  function get_entity_name() {
    return $this->get_entity()->name;
  }

  function get_entity_fields() {
    return array_keys((array)$this->get_entity()->fields);
  }

  function get_ids() {
    $return = [];
    foreach ($this->get_entity()->get_ids() as $c_id) {
      $return[$c_id] = $this->values[$c_id];
    }
    return $return;
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