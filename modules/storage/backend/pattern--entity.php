<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\modules\storage\storages_factory as storages;
          class entity {

  public $name;
  public $storage_id;
  public $charset;
  public $fields;
  public $indexes;

  function get_name()         {return $this->name;}
  function get_storage_id()   {return $this->storage_id;}
  function get_fields_info()  {return $this->fields;}
  function get_indexes_info() {return $this->indexes;}
  function get_ids()          {return array_keys((array)$this->indexes['primary']->fields);}
  function get_fields()       {return array_keys((array)$this->fields);}

  function select_set($conditions = [], $order = [], $count = 0, $offset = 0) {
    $storage = storages::get($this->storage_id);
    return $storage->select_instance_set($this, $conditions, $order, $count, $offset);
  }

  function install() {
    $storage = storages::get($this->storage_id);
    return $storage->install_entity($this);
  }

  function uninstall() {
    $storage = storages::get($this->storage_id);
    return $storage->uninstall_entity($this);
  }

}}