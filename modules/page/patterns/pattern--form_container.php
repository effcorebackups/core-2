<?php

namespace effectivecore {
          use \effectivecore\translate_factory as translations;
          class form_container extends node {

  public $template = 'form_container';
  public $tag_name = 'x-container';
  public $title;
  public $description;

  function render() {
    return (new template($this->template, [
      'attributes'  => factory::data_to_attr($this->attribute_select(), ' '),
      'tag_name'    => $this->tag_name,
      'title'       => $this->render_self(),
      'content'     => $this->render_children($this->children),
      'description' => $this->render_description()
    ]))->render();
  }

  function render_self() {
    return $this->title ? (new markup('x-title', [],
      $this->title
    ))->render() : '';
  }

  function render_description() {
    return $this->description ? (new markup('x-description', [], new markup('p', [],
      is_string($this->description) ? translations::get($this->description) : $this->description
    )))->render() : '';
  }

}}