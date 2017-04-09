<?php

namespace effectivecore {
          class menu_item extends \effectivecore\node {

  function render() {
    $rendered_children = $this->render_children($this->children);
    return count($rendered_children) ?
      (new html('li', [], [$this->render_self(), new html('ul', [], $rendered_children)]))->render() :
      (new html('li', [], [$this->render_self()]))->render();
  }

  protected function render_self() {
    $attr = clone $this->attributes;
    if (isset($attr->href)) {
      $attr->href = token::replace($attr->href);
      if (urls::is_active($attr->href)) {
        $attr->class = isset($attr->class) ? $attr->class.' active' : 'active';
      }
    }
    return (
      new markup('a', (array)$attr, token::replace($this->title))
    )->render();
  }

}}