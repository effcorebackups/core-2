<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effectivecore {
          class text {

  public $text = '';
  public $args = [];
  public $weight = 0;

  function __construct($text = '', $args = [], $weight = 0) {
    if ($text !== '') $this->text   = $text;
    if ($args)        $this->args   = $args;
    if ($weight)      $this->weight = $weight;
  }

  function render() {
    return translation::get($this->text, $this->args);
  }

}}