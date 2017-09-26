<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          class form_container_radios extends \effectivecore\form_container {

  public $values = [];
  public $input_attributes = [];
  public $field_tag_name = 'x-field';
  public $field_title_tag_name = 'label';
  public $field_title_position = 'bottom';
  public $checked = [];
  public $disabled = [];

  function build() {
    $this->attribute_insert('class', ['boxes' => 'boxes', 'radios' => 'radios']);
    foreach ($this->values as $value => $title) {
      $this->input_insert($title, ['value' => $value]);
    }
  }

  function input_insert($title = null, $attr = [], $new_id = null) {
    $input = new markup_simple('input', ['type' => 'radio'] + $attr + $this->attribute_select('', 'input_attributes'));
    $value = $input->attribute_select('value');
    if (isset($this->checked[$value]))  $input->attribute_insert('checked', 'checked');
    if (isset($this->disabled[$value])) $input->attribute_insert('disabled', 'disabled');
    $field = new form_field( $this->field_tag_name, $title );
    $field->title_tag_name = $this->field_title_tag_name;
    $field->title_position = $this->field_title_position;
    $field->child_insert($input, 'default');
    return $this->child_insert($field, $new_id);
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