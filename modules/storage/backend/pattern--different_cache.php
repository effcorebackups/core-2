<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effectivecore {
          class different_cache {

  public $cache_name;

  function __construct($cache_name = '') {
    if ($cache_name) $this->cache_name = $cache_name;
  }

  function get_cache_name() {
    return $this->cache_name;
  }

}}