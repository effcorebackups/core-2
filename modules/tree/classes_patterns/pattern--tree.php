<?php

namespace effectivecore {
          class tree extends \effectivecore\node {

  public $title;

  function __construct($title = '', $attributes = null, $children = null, $weight = 0) {
    parent::__construct($attributes, $children, $weight);
    $this->title = $title;
  }

  function render() {
    $rendered_children = (new template('tree_children', [
      'children' => $this->render_children($this->children)
    ]))->render();
    return (new template('tree', [
      'attributes' => factory::data_to_attr($this->attributes, ' '),
      'self'       => $this->render_self(),
      'children'   => $rendered_children
    ]))->render();
  }

  function render_self() {
    return (new template('tree_self', [
      'title' => $this->title
    ]))->render();
  }

}}