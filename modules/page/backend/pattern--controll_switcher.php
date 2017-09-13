<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          class control_switcher extends \effectivecore\markup {

  public $tag_name = 'x-switcher-control';
  public $state = false;

  function __construct($state = false) {
    $this->state = $state;
    parent::__construct();
  }

  function render() {
    $this->attribute_insert('x-state', $this->state ? 'on' : 'off');
    $this->child_insert(new markup('x-switcher', [], new text()));
    return parent::render();
  }

}}