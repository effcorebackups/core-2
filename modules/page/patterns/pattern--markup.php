<?php

namespace effectivecore {
          use \effectivecore\translate_factory as translations;
          class markup extends node {

  public $tag_name = 'div';

  function __construct($tag_name = '', $attributes = [], $children = [], $weight = 0) {
    if ($tag_name) $this->tag_name = $tag_name;
    parent::__construct($attributes, $children, $weight);
  }

  function child_insert($child, $id = null) {
    return parent::child_insert(
      is_string($child) ? new text($child) : $child, $id
    );
  }

  function render() {
    $template = $this->template ?: (count($this->children) ? 'html_element' : 'html_element_simple');
    return (new template($template, [
      'tag_name'   => $this->tag_name,
      'attributes' => factory::data_to_attr($this->attribute_select()),
      'content'    => $this->render_children($this->children)
    ]))->render();
  }

  function render_required_mark() {
    return (new markup('b', ['class' => ['required' => 'required']], '*'))->render();
  }

  function render_description() {
    $return = [];
    if (!empty($this->description))             $return[] = (new markup('p', [], is_string($this->description) ? translations::get($this->description) : $this->description))->render();
    if (!empty($this->attributes['minlength'])) $return[] = (new markup('p', ['class' => ['minlength' => 'minlength']], translations::get('Field should contain minimum %%_lenght symbols.', ['lenght' => $this->attributes['minlength']])))->render();
    if (!empty($this->attributes['maxlength'])) $return[] = (new markup('p', ['class' => ['maxlength' => 'maxlength']], translations::get('Field should contain maximum %%_lenght symbols.', ['lenght' => $this->attributes['maxlength']])))->render();
    return count($return) ? (new markup('x-description', [], implode($return)))->render() : '';
  }

}}