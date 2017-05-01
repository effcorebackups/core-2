<?php

namespace effectivecore {
          use \effectivecore\settings_factory as settings;
          use \effectivecore\modules\storage\storage_factory as storage;
          class entity_instance {

  public $name;
  public $fields;

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

}}