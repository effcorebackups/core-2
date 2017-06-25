<?php

namespace effectivecore {
          class tree extends \effectivecore\node {

  public $title;
  public $template = 'tree';

  function __construct($title = '', $attributes = null, $children = null, $weight = 0) {
    parent::__construct($attributes, $children, $weight);
    $this->title = $title;
  }

  function render() {
    return (new template($this->template, [
      'attributes' => factory::data_to_attr($this->attributes, ' '),
      'self'       => $this->render_self(),
      'children'   => $this->render_children($this->children)
    ]))->render();
  }

}}