<?php

namespace effectivecore {
          use \effectivecore\modules\user\access;
          class menu extends \effectivecore\folder {

  function render() {
    $rendered = [];
    foreach ($this->children as $c_child) {
      if (!isset($c_child->access) ||
          (isset($c_child->access) && access::check($c_child->access))) {
        $rendered[] = $c_child->render();
      }
    }
    return (new html('menu', ['class' => $this->attributes->class], new html('ul', [], $rendered)))->render();
  }

}}