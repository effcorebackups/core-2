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
    return (new markup('label', [], [
      $this->title,
      $this->render_required_mark()
    ]))->render();
  }

  function render_required_mark() {
    return !empty($this->attribute_select('required')) ? (
      new markup('b', ['class' => ['required' => 'required']], '*')
    )->render() : '';
  }

  function render_description() {
    $descriptions = [];
    if (!empty($this->description))                   $descriptions[] = (new markup('p', [], is_string($this->description) ? translations::get($this->description) : $this->description))->render();
    if (!empty($this->attribute_select('minlength'))) $descriptions[] = (new markup('p', ['class' => ['minlength' => 'minlength']], translations::get('Field should contain minimum %%_lenght symbols.', ['lenght' => $this->attribute_select('minlength')])))->render();
    if (!empty($this->attribute_select('maxlength'))) $descriptions[] = (new markup('p', ['class' => ['maxlength' => 'maxlength']], translations::get('Field should contain maximum %%_lenght symbols.', ['lenght' => $this->attribute_select('maxlength')])))->render();
    return count($descriptions) ? (new markup('x-description', [], implode($descriptions)))->render() : '';
  }

}}