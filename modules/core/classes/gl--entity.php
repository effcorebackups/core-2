<?php

namespace effectivecore {
          use \effectivecore\modules\storage\storage_factory;
          use \effectivecore\modules\storage\db;
          class entity {

  function install() {
    $storage = storage_factory::get_instance($this->storage_id);
    $storage->install_entity($this);
  }

  function uninstall() {
    $storage = storage_factory::get_instance($this->storage_id);
    $storage->uninstall_entity($this);
  }

}}