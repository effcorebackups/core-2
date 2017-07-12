<?php

namespace effectivecore {
          use \effectivecore\translate_factory as translations;
          class form_field_radios extends form_field {

  public $tag_name = 'x-field';

  function render() {
    $this->attributes['class']['is-box'] = 'is-box'; # @todo: use attribute_insert
    return parent::render();
  }

}}