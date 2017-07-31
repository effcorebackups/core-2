<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\translate_factory as translations;
          class form_element extends markup {

  public $template_element = 'form_element';
  public $template_title = 'form_title';
  public $title = '';
  public $description = '';

  function __construct($tag_name = '', $title = '', $description = '', $attributes = [], $children = [], $weight = 0) {
    if ($title)       $this->title       = $title;
    if ($description) $this->description = $description;
    parent::__construct($tag_name, $attributes, $children, $weight);
  }

  function render() {
    $is_right = !empty($this->title_position) && $this->title_position == 'right';
    return (new template($this->template_element, [
      'title_t'     => $is_right ? '' : $this->render_self(),
      'title_b'     => $is_right ?      $this->render_self() : '',
      'element'     => parent::render(),
      'description' => $this->render_description()
    ]))->render();
  }

  function render_self() {
    return empty($this->title) ? '' : (new template($this->template_title, [
      'tag_name'      => 'label',
      'title'         => translations::get($this->title),
      'required_mark' => $this->attribute_select('required') ? $this->render_required_mark() : ''
    ]))->render();
  }

}}