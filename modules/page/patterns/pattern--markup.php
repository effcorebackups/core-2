<?php

namespace effectivecore {
          class markup extends node {

  public $tag_name;

  function __construct($tag_name = 'div', $attributes = null, $children = null, $weight = 0) {
    parent::__construct($attributes, $children, $weight);
    $this->tag_name = $tag_name;
  }

  function child_insert($child, $id = null) {
    return parent::child_insert(
      is_string($child) ? new text($child) : $child, $id
    );
  }

  function render() {
    $rendered_children = $this->render_children($this->children);
    $template = $this->template ?: (strlen($rendered_children) ? 'html_element' : 'html_element_simple');
    return (new template($template, [
      'tag_name'   => $this->tag_name,
      'attributes' => factory::data_to_attr($this->attributes, ' '),
      'content'    => $rendered_children
    ]))->render();
  }

}}