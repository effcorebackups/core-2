<?php

namespace effectivecore {
          class menu_item extends \effectivecore\node {

  function render() {
    $rendered_children = '';
    if (count($this->children)) {
      $rendered_children = (new template('menu_item_children', [
        'children' => implode(nl, $this->render_children($this->children))
      ]))->render();
    }
    return (new template('menu_item', [
      'attributes' => implode(' ', factory::data_to_attr($this->attributes)),
      'self'       => $this->render_self(),
      'children'   => $rendered_children
    ]))->render();
  }

  protected function render_self() {
    $attr = clone $this->attributes;
    if (isset($attr->href)) {
      $attr->href = token::replace($attr->href);
      if (urls::is_active($attr->href)) {
        $attr->class = isset($attr->class) ? $attr->class.' active' : 'active';
      }
    }
    return (new template('menu_item_self', [
      'attributes' => implode(' ', factory::data_to_attr($attr)),
      'title' => token::replace($this->title)
    ]))->render();
  }

}}