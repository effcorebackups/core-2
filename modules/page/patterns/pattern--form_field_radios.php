<?php

namespace effectivecore {
          class form_field_radios extends form_container implements call_construct {

  function __construct($tag_name = '', $title = '', $description = '', $attributes = [], $children = [], $weight = 0) {
    parent::__construct($tag_name, $title, $description, $attributes, $children, $weight);
    $this->attribute_insert('class', ['boxes' => 'boxes', 'radios' => 'radios']);
    foreach ($this->values as $value => $title) {
      $this->child_insert(new form_field(), $value);
      $this->child_select($value)->child_insert(new form_element('input', $title, '', ['type' => 'radio', 'name' => $value]), 'default');
      $this->child_select($value)->child_select('default')->title_position = 'right';
    }
  }

}}