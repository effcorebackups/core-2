<?php

namespace effectivecore {
          use \effectivecore\translate_factory as translations;
          class form_field_checkboxes extends form_field {

  public $template = 'form_field';
  public $tag_name = 'x-field';
  public $title;
  public $description;

  function render() {
    $this->attributes['class']['has-box'] = 'has-box'; # @todo: use attribute_insert
    return parent::render();
  }

}}