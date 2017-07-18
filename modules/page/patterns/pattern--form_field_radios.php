<?php

namespace effectivecore {
          class form_field_radios extends form_container {

  public $values = [];

  function init() {
    $this->attribute_insert('class', ['boxes' => 'boxes', 'radios' => 'radios']);
    foreach ($this->values as $value => $title) {
      $this->child_insert(new form_field(), $value);
      $this->child_select($value)->child_insert(new form_element('input', $title, '', ['type' => 'radio', 'name' => $this->attribute_select('name'), 'value' => $value]), 'default');
      $this->child_select($value)->child_select('default')->title_position = 'right';
    }
  }

}}