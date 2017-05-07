<?php

namespace effectivecore {
          use \effectivecore\modules\storage\storage_factory as storage;
          class entity {

  public $name;
  public $fields;

  function get_name()         {return $this->name;}
  function get_fields_info()  {return $this->fields;}
  function get_indexes_info() {return $this->indexes;}
  function get_ids()          {return $this->indexes['primary']->fields;}
  function get_fields()       {return array_keys((array)$this->fields);}

  function install() {
    $storage = storage::get_instance($this->storage_id);
    $storage->install_entity($this);
  }

  function uninstall() {
    $storage = storage::get_instance($this->storage_id);
    $storage->uninstall_entity($this);
  }

}}