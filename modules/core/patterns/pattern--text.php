<?php

namespace effectivecore {
          use \effectivecore\translate_factory as translations;
          class text {

  public $text;
  public $args = [];
  public $weight;

  function __construct($text = '', $args = [], $weight = 0) {
    $this->text = $text;
    $this->args = $args;
    $this->weight = $weight;
  }

  function render() {
    return translations::get($this->text, $this->args);
  }

}}