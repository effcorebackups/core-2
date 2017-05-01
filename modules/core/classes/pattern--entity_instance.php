<?php

namespace effectivecore {
          use \effectivecore\settings_factory as settings;
          use \effectivecore\modules\storage\storage_factory as storage;
          class entity_instance {

  public $name;
  public $fields;
  public $is_loaded = false;

  function __construct($name = '', $fields = null) {
    $this->name = $name;
    if (is_null($this->fields)) {
      $this->fields = new \StdClass();
    }
    if (is_array($fields)) {
      foreach ($fields as $c_key => $c_value) {
        $this->fields->{$c_key} = $c_value;
      }
    }
  }

  function select() {
    $entity = entity_factory::get_entity($this->name);
    $storage = storage::get_instance($entity->storage_id);
    $storage->select_instance($this);
  }

  function insert() {
    $entity = entity_factory::get_entity($this->name);
    $storage = storage::get_instance($entity->storage_id);
    $storage->insert_instance($this);
  }

  function update() {
    $entity = entity_factory::get_entity($this->name);
    $storage = storage::get_instance($entity->storage_id);
    $storage->update_instance($this);
  }

  function delete() {
    $entity = entity_factory::get_entity($this->name);
    $storage = storage::get_instance($entity->storage_id);
    $storage->delete_instance($this);
  }

  function load() {
    $entity = entity_factory::get_entity($this->name);
    $storage = storage::get_instance($entity->storage_id);
    $conditions = [];
    foreach ($entity->indexes['primary']->fields as $c_name) {
      $conditions[$c_name] = $this->fields->{$c_name};
    }
    $data = $storage->load_data($this->name, array_keys((array)$entity->fields), $conditions);
    if ($data) {
      foreach ($data as $c_key => $c_value) {
        $this->fields->{$c_key} = $c_value;
      }
      $this->is_loaded = true;
      return $this;
    }
  }

  function save() {
  }

}}