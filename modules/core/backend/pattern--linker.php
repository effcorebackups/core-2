<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\modules\storage\storages_factory as storages;
          class linker {

  public $npath;
  private $p;

  function __construct($npath = '') {
    if ($npath) $this->npath = $npath;
  }

  function get() {
    return $this->p ?: ($this->p = factory::npath_get_object($this->npath, storages::get('settings')->select()));
  }

}}