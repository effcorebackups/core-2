<?php

namespace effectivecore {
          class menu extends \effectivecore\folder {

  function render() {
    return (new html('menu', ['class' => $this->attributes->class], new html('ul', [], $this->children)))->render();
  }

}}