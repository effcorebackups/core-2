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

  function get_entity_name() {
    return $this->entity->get()->name;
  }

  function get_entity_fields() {
    return array_keys((array)$this->entity->get()->fields);
  }

  function get_ids() {
    $return = [];
    foreach ($this->entity->get()->get_ids() as $c_id) {
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