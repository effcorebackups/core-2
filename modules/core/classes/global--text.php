<?php

namespace effectivecore {
          class text {

  public $data;
  public $weight;

  function __construct($data = '', $weight = 0) {
    $this->data = $data;
    $this->weight = $weight;
  }

  function render() {
    return translate::t($this->data);
  }

}}