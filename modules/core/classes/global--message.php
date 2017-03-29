<?php

namespace effectivecore {
          class message {

  public $type;
  public $content;

  function __construct($content, $type = 'notice') {
    $this->type = $type;
    $this->content = $content;
  }

  function render() {
    return (new html('li', [], $this->content))->render();
  }

}}