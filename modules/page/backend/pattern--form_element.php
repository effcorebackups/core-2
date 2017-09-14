<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\translations_factory as translations;
          class form_element extends \effectivecore\markup {

  public $wr_template = 'form_element';
  public $wr_title_template = 'form_title';
  public $title = null;
  public $description = null;

  function __construct($tag_name = null, $title = null, $description = null, $attributes = [], $weight = 0) {
    if ($title)       $this->title       = $title;
    if ($description) $this->description = $description;
    parent::__construct($tag_name, $attributes, [], $weight);
  }

  function render() {
    $is_right = !empty($this->title_position) && $this->title_position == 'right';
    return (new template($this->wr_template, [
      'title_t'     => $is_right ? '' : $this->render_self(),
      'title_b'     => $is_right ?      $this->render_self() : '',
      'content'     => parent::render(),
      'description' => $this->render_description()
    ]))->render();
  }

  function render_self() {
    return empty($this->title) ? '' : (new template($this->wr_title_template, [
      'tag_name'      => 'label',
      'title'         => translations::get($this->title),
      'required_mark' => $this->attribute_select('required') ? $this->render_required_mark() : ''
    ]))->render();
  }

}}