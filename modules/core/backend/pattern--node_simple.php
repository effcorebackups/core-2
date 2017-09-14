<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          class node_simple {

  public $template = null;
  public $attributes = [];
  public $weight = 0;

  function __construct($attributes = [], $weight = 0) {
    $this->weight = $weight;
    foreach ($attributes as $id => $c_attribute) {
      $this->attribute_insert($id, $c_attribute);
    }
  }

  ##################
  ### attributes ###
  ##################

  function attribute_select($key = '') {
    if ($key) {
      return isset($this->attributes[$key]) ?
                   $this->attributes[$key] : null;
    } else {
      return $this->attributes;
    }
  }

  function attribute_insert($key, $data) {
    if (is_array($data)) {
      foreach ($data as $c_key => $c_value) {
        $this->attributes[$key][$c_key] = $c_value;
      }
    } else {
      $this->attributes[$key] = $data;
    }
  }

  function attribute_delete($key) {
    unset($this->attributes[$key]);
  }

  ##############
  ### render ###
  ##############

  function render() {
    if ($this->template) {
      return (new template($this->template, [
        'attributes' => factory::data_to_attr($this->attribute_select()),
        'self'       => $this->render_self(),
      ]))->render();
    } else {
      return $this->render_self();
    }
  }

  function render_self() {
    return isset($this->title) ?
                 $this->title : '';
  }

}}