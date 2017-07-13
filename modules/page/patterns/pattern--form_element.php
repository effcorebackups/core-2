<?php

namespace effectivecore {
          use \effectivecore\translate_factory as translations;
          class form_element extends markup {

  public $tag_name    = 'div';
  public $title       = '';
  public $description = '';

  function __construct($tag_name = '', $title = '', $description = '', $attributes = [], $children = [], $weight = 0) {
    if ($title)       $this->title       = $title;
    if ($description) $this->description = $description;
    parent::__construct($tag_name, $attributes, $children, $weight);
  }

  function render() {
    if (!empty($this->title_position) && $this->title_position == 'right') {
      return parent::render().
             $this->render_self().
             $this->render_description();
    } else {
      return $this->render_self().
             parent::render().
             $this->render_description();
    }
  }

  function render_self() {
    return empty($this->title) ? '' : (new template('form_label', [
      'tag_name'      => 'label',
      'label'         => translations::get($this->title),
      'required_mark' => $this->attribute_select('required') ? $this->render_required_mark() : ''
    ]))->render();
  }

}}