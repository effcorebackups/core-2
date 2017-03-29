<?php

namespace effectivecore {
          class menu_item extends \effectivecore\folder {

  function render() {
    $attr = (array)$this->attributes;
    if (isset($attr['href'])) {
      $attr['href'] = token::replace($attr['href']);
      if (urls::is_active($attr['href'])) {
        $attr['class'] = isset($attr['class']) ? $attr['class'].' active' : 'active';
      }
    }
    $rendered = [];
    foreach (factory::array_sort_by_weight($this->children) as $c_child) {
      $rendered[] = $c_child->render();
    }
    return count($rendered) ? (new html('li', [], [new html('a', $attr, token::replace($this->title)), new html('ul', [], $rendered)]))->render() :
                              (new html('li', [], [new html('a', $attr, token::replace($this->title))]))->render();
  }

}}