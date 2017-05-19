<?php

namespace effectivecore {
          use \effectivecore\modules\storage\storage_factory as storage;
          class entity_instance {

  public $entity;
  public $values;

  function __construct($npath = '', $values = []) {
    $this->values = $values;
    if ($npath) {
      $this->set_npath($npath);
    }
  }

  function __get($name)         {return $this->values[$name];}
  function __set($name, $value) {$this->values[$name] = $value;}

  function set_npath($npath)   {$this->entity = new linker($npath);}
  function set_values($values) {$this->values = $values;}

  function get_name()   {return $this->entity->get()->get_name();}
  function get_fields() {return $this->entity->get()->get_fields();}
  function get_ids()    {return $this->entity->get()->get_ids();}
  function get_values($names = []) {
    if (count($names)) {
      $values = [];
      foreach ($names as $c_name) {
        $values[$c_name] = $this->values[$c_name];
      }
      return $values;
    } else {
      return $this->values;
    }
  }

  function select($custom_ids = []) {
    $storage = storage::get($this->entity->get()->storage_id);
    return $storage->select_instance($this, $custom_ids);
  }

  function insert() {
    $storage = storage::get($this->entity->get()->storage_id);
    return $storage->insert_instance($this);
  }

  function update() {
    $storage = storage::get($this->entity->get()->storage_id);
    return $storage->update_instance($this);
  }

  function delete() {
    $storage = storage::get($this->entity->get()->storage_id);
    return $storage->delete_instance($this);
  }

}}