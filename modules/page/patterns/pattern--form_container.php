<?php

namespace effectivecore {
          class form_container extends node {

  public $template = 'form_container';
  public $tag_name = 'x-container';
  public $title;
  public $description;

  function render() {
    return (new template($this->template, [
      'attributes'  => factory::data_to_attr($this->attributes, ' '),
      'tag_name'    => $this->tag_name,
      'title'       => $this->title ? (new markup('x-title', [], $this->title))->render() : '',
      'description' => $this->description ? (new markup('x-description', [], $this->description))->render() : '',
      'children'    => $this->render_children($this->children)
    ]))->render();
  }

}}