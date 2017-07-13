<?php

namespace effectivecore {
          class markup extends node {

  public $tag_name = 'div';

  function __construct($tag_name = '', $attributes = [], $children = [], $weight = 0) {
    if ($tag_name) $this->tag_name = $tag_name;
    parent::__construct($attributes, $children, $weight);
  }

  function child_insert($child, $id = null) {
    return parent::child_insert(
      is_string($child) ? new text($child) : $child, $id
    );
  }

  function render() {
    $template = $this->template ?: (count($this->children) ? 'html_element' : 'html_element_simple');
    return (new template($template, [
      'tag_name'   => $this->tag_name,
      'attributes' => factory::data_to_attr($this->attribute_select()),
      'content'    => $this->render_children($this->children)
    ]))->render();
  }

}}