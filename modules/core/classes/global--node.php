<?php

namespace effectivecore {
          class node {

  public $title;
  public $attributes;
  public $weight;
  public $children;

  public $template;          # @todo: make working
  public $template_self;     # @todo: make working
  public $template_children; # @todo: make working

  function __construct($title = '', $attributes = null, $children = null, $weight = 0) {
    $this->title = $title;
    $this->attributes = $attributes;
    $this->weight = $weight;
    if (is_array($children)) {
      foreach ($children as $id => $c_child) {
        $this->add_child($c_child, $id);
      }
    } else {
      $this->children = $children;
    }
  }

  function add_child($child, $id = null) {
    $this->children[$id ?: count($this->children)] = $child;
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
      foreach (factory::array_sort_by_weight($children) as $c_child) {
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