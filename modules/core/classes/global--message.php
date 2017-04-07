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
    return (new html('li', [], $this->data))->render();
  }

}}