<?php

namespace effectivecore {
          class form_radios extends form_container {

  public $values = [];

  function build() {
    $this->attribute_insert('class', ['boxes' => 'boxes', 'radios' => 'radios']);
    foreach ($this->values as $value => $title) {
      $this->item_insert($title, ['value' => $value]);
    }
  }

  function item_insert($title = '', $attr = []) {
    $element = new form_element('input', $title, '', $attr + ['type' => 'radio', 'name' => $this->attribute_select('name')]);
    $element->title_position = 'right';
    $this->child_insert(new form_field(), $attr['value']);
    $this->child_select($attr['value'])->child_insert($element, 'default');
  }

  function default_set($value) {
    foreach ($this->children as $c_child) {
      $c_radio = $c_child->child_select('default');
      if ($c_radio->attribute_select('value') == $value) {
        return $c_radio->attribute_insert('checked', 'checked');
      }
    }
  }

}}