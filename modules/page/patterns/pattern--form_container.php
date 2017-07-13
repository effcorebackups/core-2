<?php

namespace effectivecore {
          use \effectivecore\translate_factory as translations;
          class form_container extends markup {

  public $template    = 'form_container';
  public $tag_name    = 'x-container';
  public $title       = '';
  public $description = '';

  function render() {
    return (new template($this->template, [
      'attributes'  => factory::data_to_attr($this->attribute_select()),
      'tag_name'    => $this->tag_name,
      'title'       => $this->render_self(),
      'children'    => $this->render_children($this->children),
      'description' => $this->render_description()
    ]))->render();
  }

  function render_self() {
    $title_tag_name = $this->tag_name == 'fieldset' ? 'legend' : 'x-title';
    return empty($this->title) ? '' : (new markup($title_tag_name, [], [
      $this->title,
      $this->attribute_select('required') ? $this->render_required_mark() : ''
    ]))->render();
  }

  function render_required_mark() {
    return (new markup('b', ['class' => ['required' => 'required']], '*'))->render();
  }

  function render_description() {
    return empty($this->description) ? '' : (
      new markup('x-description', [],
        new markup('p', [], is_string($this->description) ?
                    translations::get($this->description) :
                                      $this->description)))->render();
  }

}}