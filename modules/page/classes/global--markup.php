<?php

namespace effectivecore {
          class markup extends dom_node {

  public $type;

  function __construct($type = 'div', $attributes = null, $children = null, $weight = 0) {
    parent::__construct($attributes, $children, $weight);
    $this->type = $type;
  }

  function render() {
    $rendered_children = $this->render_children($this->children);    
    return (new template(count($rendered_children) ? 'html_element' : 'html_element_simple', [
      'type'       => $this->type,
      'attributes' => implode(' ', factory::data_to_attr($this->attributes)),
      'content'    => implode(nl, $rendered_children)
    ]))->render();
  }

}}