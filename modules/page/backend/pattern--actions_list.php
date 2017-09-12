<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          class actions_list extends markup {

  public $tag_name = 'x-actions-list';
  public $values = [];
  public $active = [];

  function __construct($values = [], $active = [], $attributes = [], $weight = 0) {
    $this->values = factory::array_values_map_to_keys($values);
    $this->active = factory::array_values_map_to_keys($active);
    parent::__construct($attributes, [], $weight);
  }

  function render() {
    foreach ($this->values as $c_value) {
      $c_attr = isset($this->active[$c_value]) ? ['class' => ['active']] : [];
      $this->child_insert(
        new markup('x-action', $c_attr, [
          new markup('a', ['href' => '?action='.$c_value], $c_value)
        ])
      );
    }
    return parent::render();
  }

}}