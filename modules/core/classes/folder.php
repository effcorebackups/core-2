<?php

namespace effectivecore {
          class folder {

  public $root;
  public $title;
  public $attributes;
  public $weight;
  public $children = [];

  function __construct($title = '', $attributes = [], $weight = 0) {
    $this->title = $title;
    $this->attributes = $attributes;
    $this->weight = $weight;
  }

  function add_child($root, $title = '', $attributes = [], $weight = 0) {
    $c_folder = &$this->children;
    foreach ($levels = explode('/', $root) as $c_level) {
      if (!isset($c_folder[$c_level])) $c_folder[$c_level] = new static();
      if ($c_level == end($levels)) {
        $c_folder[$c_level]->root = $root;
        $c_folder[$c_level]->title = $title;
        $c_folder[$c_level]->attributes = $attributes;
        $c_folder[$c_level]->weight = $weight;
      }
      $c_folder = &$c_folder[$c_level]->children;
    }
  }

  function render() {
    $rendered = [];
    foreach ($this->children as $c_child) {
      $rendered[] = $c_child->render();
    }
    return count($rendered) ? (new html('li', [], [new html('span', [], $this->title), new html('ul', [], $rendered)]))->render() :
                              (new html('li', [], [new html('span', [], $this->title)]))->render();
  }

}}