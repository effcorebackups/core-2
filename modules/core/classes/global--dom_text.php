<?php

namespace effectivecore {
          class dom_text {

  public $data;
  public $weight;

  function __construct($data = '', $weight = 0) {
    $this->data = $data;
    $this->weight = $weight;
  }

  function render() {
    return $this->data;
  }

}}