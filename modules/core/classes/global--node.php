<?php

namespace effectivecore {
          class node {

  public $title;
  public $attributes;
  public $weight = 0;
  public $children = [];

  function __construct($title = '', $attributes = null, $weight = 0) {
    $this->title = $title;
    $this->attributes = $attributes;
    $this->weight = $weight;
  }

  function render() {
    return $this->render_self().implode("\n",
           $this->render_children($this->children));
  }

  protected function render_self() {
    return $this->title;
  }

  protected function render_children($children) {
    $rendered = [];
    if (is_array($children)) {
      foreach ($children as $c_child) {
        $rendered[] = $this->render_child($c_child);
      }
    } elseif (is_string($children)) {
      $rendered[] = $children;
    }
    return $rendered;
  }

  protected function render_child($child) {
    return method_exists($child, 'render') ? $child->render() :
                                             $child;
  }

}}