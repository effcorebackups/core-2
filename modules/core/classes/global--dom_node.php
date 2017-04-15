<?php

namespace effectivecore {
          class dom_node {

  public $attributes;
  public $weight;
  public $children;

  public $template;          # @todo: make working
  public $template_self;     # @todo: make working
  public $template_children; # @todo: make working

  function __construct($attributes = null, $children = null, $weight = 0) {
    $this->attributes = $attributes;
    $this->weight = $weight;
    if ($children) {
      if (is_array($children)) {
        foreach ($children as $id => $c_child) {
          $this->add_child($c_child, $id);
        }
      } else {
        $this->add_child($children);
      }
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
    return '';
  }

  protected function render_children($children, $join = true) {
    $rendered = [];
    if (is_array($children)) {
      foreach (factory::array_sort_by_weight($children) as $c_child) {
        $rendered[] = $this->render_child($c_child);
      }
    } else {
      $rendered[] = $this->render_child($children);
    }
    return $join ? implode(nl, $rendered) :
                               $rendered;
  }

  protected function render_child($child) {
    return method_exists($child, 'render') ? $child->render() :
                                             $child;
  }

}}