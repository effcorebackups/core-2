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
  function get_fields()       {return factory::array_values_map_to_keys(array_keys((array)$this->fields));}

  function get_auto_increment() {
    foreach ($this->fields as $name => $info) {
      if (!empty($info->auto_increment)) {
        return $name;
      }
    }
  }

  function get_keys($only_unique = true) {
    $keys = [];
    foreach ($this->indexes as $c_index) {
      if ($c_index->type == 'unique key'  ||
          $c_index->type == 'primary key' ||
         ($c_index->type == 'key' && !$only_unique)) {
        $keys += $c_index->fields;
      }
    }
    return factory::array_values_map_to_keys($keys);
  }

  function install() {
    $storage = storages::get($this->storage_id);
    return $storage->install_entity($this);
  }

  function uninstall() {
    $storage = storages::get($this->storage_id);
    return $storage->uninstall_entity($this);
  }

  function select_instance_set($conditions = [], $order = [], $count = 0, $offset = 0) {
    $storage = storages::get($this->storage_id);
    return $storage->select_instance_set($this, $conditions, $order, $count, $offset);
  }

  function select_instance() {}
  function insert_instance() {}
  function delete_instance() {}

}}