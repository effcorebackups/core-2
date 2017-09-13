<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          class pager extends \effectivecore\node {

  public $id;
  public $has_error = false;

  function __construct() {
  }

  function get_current_page_num() {
    return 1;
  }

  function render() {
    return '[PAGER IS UNDER CONSTRUCTION]';
  }

}}