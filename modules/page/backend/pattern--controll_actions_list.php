<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effectivecore {
          class control_actions_list extends \effectivecore\markup {

  public $tag_name = 'x-actions-control';
  public $title = null;
  public $actions = [];
  public $active = [];

  function __construct($actions = [], $active = [], $title = 'actions', $attributes = [], $weight = 0) {
    $this->active = factory::array_values_map_to_keys($active);
    foreach ($actions as $id => $c_action) $this->action_add($id, $c_action);
    $this->title = $title;
    parent::__construct($this->tag_name, $attributes, [], $weight);
  }

  function action_add($id, $title) {
    $this->actions[$id] = $title;
  }

  function render() {
    $this->child_insert(new markup('x-title', [], $this->title), 'title');
    $this->child_insert(new markup('x-action-list'), 'action_list');
    $list = $this->child_select('action_list');
    foreach ($this->actions as $c_action) {
      $c_attr = isset($this->active[$c_action]) ? ['class' => ['active']] : [];
      $list->child_insert(
        new markup('x-action', $c_attr,
          new markup('a', ['href' => '?action='.$c_action], $c_action)
        ));
    }
    return parent::render();
  }

}}