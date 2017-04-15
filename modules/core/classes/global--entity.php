<?php

namespace effectivecore {
          class entity {

  public $id;
  public $fields;

  function __construct($id, $fields = []) {
    $this->id = $id;
    foreach ($fields as $c_field) {
      $this->add_field($this->$c_field);
    }
  }

  function add_field($field) {
    $this->fields[] = $field;
  }

}}