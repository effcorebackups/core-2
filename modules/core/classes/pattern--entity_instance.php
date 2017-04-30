<?php

namespace effectivecore {
          use \effectivecore\settings_factory as settings;
          use \effectivecore\modules\storage\storage_factory as storage;
          class entity_instance {

  public $name;
  public $fields;

  static function get_entity($name) { # @todo: optimize this
    foreach (settings::$data['entities'] as $c_entities) {
      foreach ($c_entities as $c_entity) {
        if ($c_entity->name == $name) {
          return $c_entity;
        }
      }
    }
  }

  function select() {
    $entity = static::get_entity($this->name);
    $storage = storage::get_instance($entity->storage_id);
    $storage->select_entity($this);
  }

  function insert() {
    $entity = static::get_entity($this->name);
    $storage = storage::get_instance($entity->storage_id);
    $storage->insert_entity($this);
  }

  function update() {
    $entity = static::get_entity($this->name);
    $storage = storage::get_instance($entity->storage_id);
    $storage->update_entity($this);
  }

  function delete() {
    $entity = static::get_entity($this->name);
    $storage = storage::get_instance($entity->storage_id);
    $storage->delete_entity($this);
  }

}}