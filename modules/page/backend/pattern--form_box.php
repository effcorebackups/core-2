<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\translations_factory as translations;
          class form_box extends \effectivecore\markup {

  public $tag_name = 'x-form-box';
  public $template = 'form_box';
  public $title = null;
  public $description = '';

  function __construct($tag_name = null, $title = null, $description = null, $attributes = [], $children = [], $weight = 0) {
    if ($tag_name)    $this->tag_name    = $tag_name;
    if ($title)       $this->title       = $title;
    if ($description) $this->description = $description;
    parent::__construct($attributes, $children, $weight);
  }

  function render() {
    $is_bottom_title = !empty($this->title_position) &&
                              $this->title_position == 'bottom';
    return (new template($this->template, [
      'tag_name'    => $this->tag_name,
      'attributes'  => factory::data_to_attr($this->attribute_select()),
      'content'     => $this->render_children($this->children),
      'description' => $this->render_description(),
      'title_t'     => $is_bottom_title ? '' : $this->render_self(),
      'title_b'     => $is_bottom_title ?      $this->render_self() : ''
    ]))->render();
  }

  function render_self() {
    if ($this->title) {
      $required_mark = $this->attribute_select('required') ? $this->render_required_mark() : '';
      return (new markup('x-title', [], [
        $this->title, $required_mark
      ]))->render();
    }
  }

  function render_required_mark() {
    return (new markup('b', ['class' => ['required' => 'required']], '*'))->render();
  }

  function render_description() {
    if ($this->description) {
      return (new markup('x-description', [],
        new markup('p', [], is_string($this->description) ?
                    translations::get($this->description) :
                                      $this->description)
      ))->render();
    }
  }

}}