<?php

namespace effectivecore {
          class markup extends dom_node {

  public $type;

  function __construct($type = 'div', $attributes = null, $children = null, $weight = 0) {
    parent::__construct($attributes, $children, $weight);
    $this->type = $type;
  }

  function add_child($child, $id = null) {
    parent::add_child(
      is_string($child) ? new dom_text($child) : $child, $id
    );
  }

  function render() {
    $rendered_children = $this->render_children($this->children);    
    return (new template(strlen($rendered_children) ? 'html_element' : 'html_element_simple', [
      'type'       => $this->type,
      'attributes' => implode(' ', factory::data_to_attr($this->attributes)),
      'content'    => $rendered_children
    ]))->render();
  }

}}