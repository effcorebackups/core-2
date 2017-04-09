<?php

namespace effectivecore {
          class menu extends \effectivecore\node {

  function render() {
    $rendered_children = (new template('menu_children', [
      'children' => implode(nl, $this->render_children($this->children))
    ]))->render();
    return (new template('menu', [
      'attributes' => implode(' ', factory::data_to_attr($this->attributes)),
      'self'       => $this->render_self(),
      'children'   => $rendered_children
    ]))->render();
  }

  protected function render_self() {
    return (new template('menu_self', [
      'title' => $this->title
    ]))->render();
  }

}}