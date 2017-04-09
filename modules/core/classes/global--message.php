<?php

namespace effectivecore {
          class message {

  public $type;
  public $data;
  public $weight;

  function __construct($data, $type = 'notice', $weight = 0) {
    $this->type = $type;
    $this->data = $data;
    $this->weight = $weight;
  }

  function render() {
    return (new markup('li', [], $this->data))->render();
  }

}}