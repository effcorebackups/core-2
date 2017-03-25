<?php

namespace effectivecore {
          class message_n {

  public $type;
  public $content;

  function __construct($content, $type = 'notice') {
    $this->type = $type;
    $this->content = $content;
  }

  function render() {
    return (new html('div', ['class' => $this->type], $this->content))->render();
  }

}}