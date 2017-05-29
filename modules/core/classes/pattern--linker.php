<?php

namespace effectivecore {
          use \effectivecore\modules\storage\storage_factory as storage;
          class linker {

  public $npath;
  private $p;

  function __construct($npath = '') {
    $this->npath = $npath;
  }

  function get() {
    return $this->p ?: ($this->p = factory::npath_get_object($this->npath, storage::get('settings')->select()));
  }

}}