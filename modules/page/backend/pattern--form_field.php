<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\translations_factory as translations;
          class form_field extends \effectivecore\form_container {

  public $tag_name = 'x-field';
  public $title_tag_name = 'label';

  function render() {
    $element = $this->child_select('element');
    if ($element instanceof node_simple && $element->attribute_select('disabled')) $this->attribute_insert('class', ['disabled' => 'disabled']);
    if ($element instanceof node_simple && $element->attribute_select('required')) $this->attribute_insert('class', ['required' => 'required']);
    return parent::render();
  }

  function render_self() {
    $element = $this->child_select('element');
    if ($this->title) {
      $required_mark = $this->attribute_select('required') || ($element instanceof node_simple && $element->attribute_select('required')) ?
                       $this->render_required_mark() : '';
      return (new markup($this->title_tag_name, [], [
        $this->title, $required_mark
      ]))->render();
    }
  }

  function render_description() {
    $return = [];
    $element = $this->child_select('element');
    if ($element instanceof node_simple && $element->attribute_select('minlength'))       $return[] = new markup('p', ['class' => ['minlength' => 'minlength']], translations::get('Field must contain a minimum of %%_num characters.', ['num' => $element->attribute_select('minlength')]));
    if ($element instanceof node_simple && $element->attribute_select('maxlength'))       $return[] = new markup('p', ['class' => ['maxlength' => 'maxlength']], translations::get('Field must contain a maximum of %%_num characters.', ['num' => $element->attribute_select('maxlength')]));
    if ($element instanceof node_simple && $element->attribute_select('min'))             $return[] = new markup('p', ['class' => ['min' => 'min']],             translations::get('Minimal field value: %%_value.', ['value' => $element->attribute_select('min')]));
    if ($element instanceof node_simple && $element->attribute_select('max'))             $return[] = new markup('p', ['class' => ['max' => 'max']],             translations::get('Maximal field value: %%_value.', ['value' => $element->attribute_select('max')]));
    if ($element instanceof node_simple && $element->attribute_select('type') == 'range') $return[] = new markup('p', ['class' => ['cur' => 'cur']],             translations::get('Current field value: %%_value.', ['value' => (new markup('x-value', [], $element->attribute_select('value')))->render()]));
    if ($this->description)                                                               $return[] = new markup('p', [], $this->description);
    if (count($return)) {
      return (new markup($this->description_tag_name, [], $return))->render();
    }
  }

}}