<?php

namespace effectivecore {
          class markup extends node {

  public $tag_name;

  function __construct($tag_name = 'div', $attributes = null, $children = null, $weight = 0) {
    parent::__construct($attributes, $children, $weight);
    $this->tag_name = $tag_name;
  }

  function add_child($child, $id = null) {
    parent::add_child(
      is_string($child) ? new text($child) : $child, $id
    );
  }

  function render() {
    $rendered_children = $this->render_children($this->children);    
    return (new template(strlen($rendered_children) ? 'html_element' : 'html_element_simple', [
      'tag_name'   => $this->tag_name,
      'attributes' => factory::data_to_attr($this->attributes, ' '),
      'content'    => $rendered_children
    ]))->render();
  }

}}