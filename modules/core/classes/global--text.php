<?php

namespace effectivecore {
          class text {

  public $text;
  public $weight;

  function __construct($text = '', $weight = 0) {
    $this->text = $text;
    $this->weight = $weight;
  }

  function render() {
    return translate_factory::t($this->text);
  }

}}