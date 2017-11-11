<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\modules\storage\storage_factory as storage;
          class entity {

  public $name;
  public $storage_id;
  public $fields = [];
  public $constraints = [];
  public $indexes = [];

  function get_name()             {return $this->name;}
  function get_storage_id()       {return $this->storage_id;}
  function get_field_info($name)  {return $this->fields->{$name};}
  function get_fields_info()      {return $this->fields;}
  function get_indexes_info()     {return $this->indexes;}
  function get_constraints_info() {return $this->constraints;}
  function get_fields() {
    return factory::array_values_map_to_keys(
      array_keys((array)$this->fields)
    );
  }

  function get_auto_name() {
    foreach ($this->fields as $name => $info) {
      if ($info->type == 'autoincrement') {
        return $name;
      }
    }
  }

  function get_keys($primary = true, $unique = true) {
    $keys = [];
    foreach ($this->constraints as $c_cstr) {
      if (($c_cstr->type == 'primary key' && $primary) ||
          ($c_cstr->type == 'unique'      && $unique)) {
        $keys += $c_cstr->fields;
      }
    }
    return factory::array_values_map_to_keys($keys);
  }

  function install() {
    $storage = storage::get($this->storage_id);
    return $storage->install_entity($this);
  }

  function uninstall() {
    $storage = storage::get($this->storage_id);
    return $storage->uninstall_entity($this);
  }

  function select_instances($conditions = [], $order = [], $count = 0, $offset = 0) {
    $storage = storage::get($this->storage_id);
    return $storage->select_instances($this, $conditions, $order, $count, $offset);
  }

  function insert_instances() {
  # todo: make functionality
  }

  function delete_instances() {
  # todo: make functionality
  }

}}