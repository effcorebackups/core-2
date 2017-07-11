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
      'description' => $this->render_description($this->description, $this->attribute_select())
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

  function render_description($descriptions = [], $attributes = []) {
    $return = [];
    foreach ($descriptions as $c_description) {
      $return[] = (new markup('p', [], is_string($c_description) ?
                               translations::get($c_description) : $c_description)
      )->render();
    }
    if (!empty($attributes['minlength'])) $return[] = (new markup('p', ['class' => ['minlength' => 'minlength']], translations::get('Field should contain minimum %%_lenght symbols.', ['lenght' => $attributes['minlength']])))->render();
    if (!empty($attributes['maxlength'])) $return[] = (new markup('p', ['class' => ['maxlength' => 'maxlength']], translations::get('Field should contain maximum %%_lenght symbols.', ['lenght' => $attributes['maxlength']])))->render();
    return count($return) ? (new markup('x-description', [], implode($return)))->render() : '';
  }

}}