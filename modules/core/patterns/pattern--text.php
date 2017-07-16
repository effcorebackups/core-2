<?php

namespace effectivecore {
          use \effectivecore\translate_factory as translations;
          class text extends pattern {

  public $text = '';
  public $args = [];
  public $weight = 0;

  function __construct($text = '', $args = [], $weight = 0) {
    if ($text)   $this->text   = $text;
    if ($args)   $this->args   = $args;
    if ($weight) $this->weight = $weight;
  }

  function render() {
    return translations::get($this->text, $this->args);
  }

}}