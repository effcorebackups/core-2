<?php

namespace effectivecore {
          class message {

  public $type;
  public $data;

  function __construct($data, $type = 'notice') {
    $this->type = $type;
    $this->data = $data;
  }

  function render() {
    return (new markup('li', [], $this->data))->render();
  }

}}