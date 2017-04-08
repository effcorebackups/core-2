<?php

namespace effectivecore {
          use \effectivecore\modules\user\access;
          class menu extends \effectivecore\node {

  function render() {
    $rendered_children = [];
    foreach ($this->children as $c_child) {
      if (!isset($c_child->access) ||
          (isset($c_child->access) && access::check($c_child->access))) {
        $rendered_children[] = $c_child->render();
      }
    }
    return (new html('menu', ['class' => $this->attributes->class],
              new html('ul', [], $rendered_children)
           ))->render();
  }

}}