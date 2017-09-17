<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          class form_box_radios extends \effectivecore\form_box {

  public $values = [];

  function build() {
    $this->attribute_insert('class', ['boxes' => 'boxes', 'radios' => 'radios']);
    foreach ($this->values as $value => $title) {
      $this->field_insert($title, ['value' => $value]);
    }
  }

  function field_insert($title = '', $attr = []) {
 // $item = new form_element('input', $title, '', $attr + ['type' => 'radio', 'name' => $this->attribute_select('name')]);
    $item = new markup('input', $attr + ['type' => 'radio', 'name' => $this->attribute_select('name')]);
    $item->title_position = 'right';
    $this->child_insert(new form_box('x-field'), $attr['value']);
    $this->child_select($attr['value'])->child_insert($item, 'default');
  }

  function default_set($value) {
    foreach ($this->children as $c_child) {
      $c_radio = $c_child->child_select('default');
      if ($c_radio->attribute_select('value') == $value) {
        return $c_radio->attribute_insert('checked', 'checked');
      }
    }
  }

  function default_get() {
    foreach ($this->children as $c_child) {
      $c_radio = $c_child->child_select('default');
      if ($c_radio->attribute_select('checked') == 'checked') {
        return $c_radio->attribute_select('value');
      }
    }
  }

}}