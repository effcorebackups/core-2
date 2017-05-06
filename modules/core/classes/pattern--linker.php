<?php

namespace effectivecore {
          use \effectivecore\settings_factory as settings;
          class linker {

  public $npath;
  private $p;

  function __construct($npath = '') {
    $this->npath = $npath;
  }

  function get() {
    return $this->p ?: ($this->p = factory::npath_get_object($this->npath, settings::$data));
  }

}}