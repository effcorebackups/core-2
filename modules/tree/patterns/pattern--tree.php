<?php

namespace effectivecore {
          class tree extends \effectivecore\node {

  public $title;
  public $template = 'tree';

  function __construct($title = '', $attributes = [], $children = [], $weight = 0) {
    parent::__construct($attributes, $children, $weight);
    $this->title = $title;
  }

}}