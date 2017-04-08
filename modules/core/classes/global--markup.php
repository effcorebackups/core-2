<?php

namespace effectivecore {
          class markup extends node {

  public $type;

  function __construct($type = 'div', $attributes = null, $weight = 0) {
    parent::__construct(null, $attributes, $weight);
    unset($this->title);
    $this->type = $type;
  }

  function render() {
    $rendered_children = $this->render_children($this->children);
    $template = new template(count($rendered_children) ? 'html_element' : 'html_element_simple');
    $template->set_var('type', $this->type);
    $template->set_var('attributes', implode(' ', factory::data_to_attr($this->attributes)));
    $template->set_var('content', implode(nl, $rendered_children));
    return $template->render();
  }

}}