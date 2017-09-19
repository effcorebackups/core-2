<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          class form_container_checkboxes extends \effectivecore\form_container {

  public $values = [];
  public $checkboxes_attributes = [];
  public $each_field_tag_name = 'x-field';
  public $each_title_tag_name = 'label';
  public $each_title_position = 'bottom';

  function build() {
    $this->attribute_insert('class', ['boxes' => 'boxes', 'checkboxes' => 'checkboxes']);
    foreach ($this->values as $value => $title) {
      $this->checkbox_insert($title, ['value' => $value]);
    }
  }

  function checkbox_insert($title = null, $attr = [], $new_id = null) {
    $input = new markup_simple('input', ['type' => 'checkbox'] + $attr + $this->attribute_select('', 'checkboxes_attributes'));
    $field = new form_field( $this->each_field_tag_name, $title );
    $field->title_tag_name = $this->each_title_tag_name;
    $field->title_position = $this->each_title_position;
    $field->child_insert($input, 'default');
    return $this->child_insert($field, $new_id);
  }

  function default_set($value) {
    foreach ($this->children as $c_field) {
      $c_checkbox = $c_field->child_select('default');
      if ($c_checkbox->attribute_select('value') == $value) {
        return $c_checkbox->attribute_insert('checked', 'checked');
      }
    }
  }

  function default_get() {
    foreach ($this->children as $c_field) {
      $c_checkbox = $c_field->child_select('default');
      if ($c_checkbox->attribute_select('checked') == 'checked') {
        return $c_checkbox->attribute_select('value');
      }
    }
  }

}}