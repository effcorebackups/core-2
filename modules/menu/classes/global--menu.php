<?php

namespace effectivecore {
          use \effectivecore\modules\user\access;
          class menu extends \effectivecore\node {

  function render() {
    return (new html('menu', ['class' => $this->attributes->class], [
              ($this->render_self()),
              (new html('ul', [], $this->render_children($this->children)))->render()
           ]))->render();
  }

  protected function render_self() {
    return (new markup('h3', ['class' => 'hidden'],
      $this->title
    ))->render();
  }

  protected function render_child($child) {
    if (!isset($child->access) ||
        (isset($child->access) && access::check($child->access))) {
      return parent::render_child($child);
    }
  }

}}