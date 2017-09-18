<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          class form_container_radios extends \effectivecore\form_container {

  public $values = [];
  public $each_field_tag_name = 'x-field';
  public $each_title_tag_name = 'label';
  public $each_title_position = 'bottom';
  public $each_name = null;

  function build() {
    $this->attribute_insert('class', ['boxes' => 'boxes', 'radios' => 'radios']);
    foreach ($this->values as $value => $title) {
      $this->radio_insert($title, ['value' => $value]);
    }
  }

  function radio_insert($title = null, $attr = [], $new_id = null) {
    $input = new markup_simple('input', $attr + ['type' => 'radio', 'name' => $this->each_name]);
    $field = new form_field( $this->each_field_tag_name, $title );
    $field->title_tag_name = $this->each_title_tag_name;
    $field->title_position = $this->each_title_position;
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