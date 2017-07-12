<?php

namespace effectivecore {
          class tree extends \effectivecore\node {

  public $title;
  public $template = 'tree';

  function __construct($title = '', $attributes = [], $children = [], $weight = 0) {
    $this->title = $title;
    parent::__construct($attributes, $children, $weight);
  }

}}