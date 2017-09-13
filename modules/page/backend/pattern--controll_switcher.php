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

}}