<?php

namespace effectivecore {
          use \effectivecore\modules\storage\storage_factory as storage;
          class entity {

  function install() {
    $storage = storage::get_instance($this->storage_id);
    $storage->install_entity($this);
  }

  function uninstall() {
    $storage = storage::get_instance($this->storage_id);
    $storage->uninstall_entity($this);
  }

  function select() {
    $storage = storage::get_instance($this->storage_id);
    $storage->select_entity($this);
  }

  function insert() {
    $storage = storage::get_instance($this->storage_id);
    $storage->insert_entity($this);
  }

  function update() {
    $storage = storage::get_instance($this->storage_id);
    $storage->update_entity($this);
  }

  function delete() {
    $storage = storage::get_instance($this->storage_id);
    $storage->delete_entity($this);
  }

}}