<?php

namespace effectivecore {
          class tree extends \effectivecore\node {

  public $title;
  public $template          = 'tree';
  public $template_self     = 'tree_self';
  public $template_children = 'tree_children';

  function __construct($title = '', $attributes = null, $children = null, $weight = 0) {
    parent::__construct($attributes, $children, $weight);
    $this->title = $title;
  }

  function render() {
    $rendered_children = (new template($this->template_children, [
      'children' => $this->render_children($this->children)
    ]))->render();
    return (new template($this->template, [
      'attributes' => factory::data_to_attr($this->attributes, ' '),
      'self'       => $this->render_self(),
      'children'   => $rendered_children
    ]))->render();
  }

  function render_self() {
    return (new template($this->template_self, [
      'title' => $this->title
    ]))->render();
  }

}}