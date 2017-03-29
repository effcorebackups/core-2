<?php

namespace effectivecore {
          class folder {

  public $title;
  public $attributes;
  public $weight;
  public $children = [];

  function __construct($title = '', $attributes = [], $weight = 0) {
    $this->title = $title;
    $this->attributes = $attributes;
    $this->weight = $weight;
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