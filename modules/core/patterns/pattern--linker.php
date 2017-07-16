<?php

namespace effectivecore {
          use \effectivecore\modules\storage\storage_factory as storages;
          class linker extends pattern {

  public $npath;
  private $p;

  function __construct($npath = '') {
    if ($npath) $this->npath = $npath;
  }

  function get() {
    return $this->p ?: ($this->p = factory::npath_get_object($this->npath, storages::get('settings')->select()));
  }

}}