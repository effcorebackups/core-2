<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          class control_actions_list extends \effectivecore\markup {

  public $tag_name = 'x-actions-control';
  public $title = null;
  public $values = [];
  public $active = [];

  function __construct($values = [], $active = [], $title = null, $attributes = [], $weight = 0) {
    $this->values = factory::array_values_map_to_keys($values);
    $this->active = factory::array_values_map_to_keys($active);
    if (!is_null($title)) $this->title = $title;
    parent::__construct($this->tag_name, $attributes, [], $weight);
  }

  function render() {
    $this->child_insert(new markup('x-title', [], !is_null($this->title) ? $this->title : 'actions'), 'title');
    $this->child_insert(new markup('x-action-list'), 'action_list');
    $list = $this->child_select('action_list');
    foreach ($this->values as $c_value) {
      $c_attr = isset($this->active[$c_value]) ? ['class' => ['active']] : [];
      $list->child_insert(
        new markup('x-action', $c_attr,
        new markup('a', ['href' => '?action='.$c_value], $c_value))
      );
    }
    return parent::render();
  }

}}