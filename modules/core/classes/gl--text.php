<?php

namespace effectivecore {
          use \effectivecore\translate_factory as translate;
          class text {

  public $text;
  public $weight;

  function __construct($text = '', $weight = 0) {
    $this->text = $text;
    $this->weight = $weight;
  }

  function render() {
    return translate::t($this->text);
  }

}}