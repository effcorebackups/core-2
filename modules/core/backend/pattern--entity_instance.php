<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\entities_factory as entities;
          use \effectivecore\modules\storage\storages_factory as storages;
          class entity_instance {

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
      $values = [];
      foreach ($names as $c_name) {
        $values[$c_name] = $this->values[$c_name];
      }
      return $values;
    } else {
      return $this->values;
    }
  }

  function get_entity()            {return entities::get($this->entity_name);}
  function get_entity_name()       {return $this->get_entity()->get_name();}
  function get_entity_fields()     {return $this->get_entity()->get_fields();}
  function get_entity_ids()        {return $this->get_entity()->get_ids();}
  function get_entity_storage_id() {return $this->get_entity()->get_storage_id();}
  function set_entity_name($entity_name) {$this->entity_name = $entity_name;}

  function select($custom_ids = []) {
    $storage = storages::get($this->get_entity_storage_id());
    return $storage->select_instance($this, $custom_ids);
  }

  function insert() {
    $storage = storages::get($this->get_entity_storage_id());
    return $storage->insert_instance($this);
  }

  function update() {
    $storage = storages::get($this->get_entity_storage_id());
    return $storage->update_instance($this);
  }

  function delete() {
    $storage = storages::get($this->get_entity_storage_id());
    return $storage->delete_instance($this);
  }

  function render() {
  }

}}