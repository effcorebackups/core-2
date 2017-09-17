<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          class form_container_radios extends \effectivecore\form_container {

  public $values = [];
  public $each_name = '';

  function build() {
    $this->attribute_insert('class', ['boxes' => 'boxes', 'radios' => 'radios']);
    foreach ($this->values as $value => $title) {
      $this->radio_insert($title, ['value' => $value]);
    }
  }

  function radio_insert($title = '', $attr = []) {
    $field = new markup('x-field');
    $field->child_insert(new markup_simple('input', $attr + ['name' => $this->each_name, 'type' => 'radio']), 'default');
    if ($title) $field->child_insert(new markup('label', [], $title));
    $this->child_insert($field);
  }

  function default_set($value) {
    foreach ($this->children as $c_field) {
      $c_radio = $c_field->child_select('default');
      if ($c_radio->attribute_select('value') == $value) {
        return $c_radio->attribute_insert('checked', 'checked');
      }
    }
  }

  function default_get() {
    foreach ($this->children as $c_field) {
      $c_radio = $c_field->child_select('default');
      if ($c_radio->attribute_select('checked') == 'checked') {
        return $c_radio->attribute_select('value');
      }
    }
  }

}}