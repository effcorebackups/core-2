<?php

namespace effectivecore {
          use \effectivecore\modules\storage\storage_factory as storage;
          class entity_instance {

  public $entity;
  public $values;

  function __construct($npath = '', $values = []) {
    $this->values = $values;
    if ($npath) {
      $this->entity = new linker($npath);
    }
  }

  function get_name()   {return $this->entity->get()->get_name();}
  function get_fields() {return $this->entity->get()->get_fields();}
  function get_ids()    {return $this->entity->get()->get_ids();}

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
    $storage = storage::get_instance($this->entity->get()->storage_id);
    return $storage->select_instance($this);
  }

  function insert() {
    $storage = storage::get_instance($this->entity->get()->storage_id);
    return $storage->insert_instance($this);
  }

  function update() {
    $storage = storage::get_instance($this->entity->get()->storage_id);
    return $storage->update_instance($this);
  }

  function delete() {
    $storage = storage::get_instance($this->entity->get()->storage_id);
    return $storage->delete_instance($this);
  }

}}