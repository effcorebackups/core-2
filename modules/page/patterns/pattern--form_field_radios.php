<?php

namespace effectivecore {
          class form_field_radios extends form_container {

  function render() {
    $this->attribute_insert('class', ['boxes', 'radios']);
    foreach ($this->values as $value => $title) {
      $c_element = new form_element('input', $title, '', ['type' => 'radio', 'name' => $value]);
      $c_element->title_position = 'right';
      $this->child_insert(new form_field('', '', '', [], ['default' => $c_element]), $value);
    }
    return parent::render();
  }

}}