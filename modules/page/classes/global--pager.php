<?php

namespace effectivecore {
          class pager {

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