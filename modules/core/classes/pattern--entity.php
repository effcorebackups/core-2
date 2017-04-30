<?php

namespace effectivecore {
          use \effectivecore\modules\storage\storage_factory as storage;
          class entity {

  public $name;
  public $fields;

  function install() {
    $storage = storage::get_instance($this->storage_id);
    $storage->install_entity($this);
  }

  function uninstall() {
    $storage = storage::get_instance($this->storage_id);
    $storage->uninstall_entity($this);
  }

}}