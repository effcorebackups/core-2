<?php

namespace effectivecore {
          class translated_text {

  public $text;

  function __construct($text = '') {
    $this->text = $text;
  }

  function render() {
    return translate::t($this->text);
  }

}}