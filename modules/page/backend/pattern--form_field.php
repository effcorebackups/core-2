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
    $default = $this->child_select('default');
    if ($default instanceof node_simple && $default->attribute_select('disabled')) $this->attribute_insert('class', ['disabled' => 'disabled']);
    if ($default instanceof node_simple && $default->attribute_select('required')) $this->attribute_insert('class', ['required' => 'required']);
    return parent::render();
  }

  function render_self() {
    $default = $this->child_select('default');
    if ($this->title) {
      $required_mark = $this->attribute_select('required') || ($default instanceof node_simple && $default->attribute_select('required')) ?
                       $this->render_required_mark() : '';
      return (new markup($this->title_tag_name, [], [
        $this->title, $required_mark
      ]))->render();
    }
  }

  function render_description() {
    $return = [];
    $default = $this->child_select('default');
    if ($default instanceof node_simple && !empty($default->attributes['minlength'])) $return[] = new markup('p', ['class' => ['minlength' => 'minlength']], translations::get('Field must contain a minimum of %%_lenght characters.', ['lenght' => $default->attributes['minlength']]));
    if ($default instanceof node_simple && !empty($default->attributes['maxlength'])) $return[] = new markup('p', ['class' => ['maxlength' => 'maxlength']], translations::get('Field must contain a maximum of %%_lenght characters.', ['lenght' => $default->attributes['maxlength']]));
    if ($default instanceof node_simple && !empty($default->attributes['min']))       $return[] = new markup('p', ['class' => ['min' => 'min']],             translations::get('Minimal field value: %%_value.', ['value' => $default->attributes['min']]));
    if ($default instanceof node_simple && !empty($default->attributes['max']))       $return[] = new markup('p', ['class' => ['max' => 'max']],             translations::get('Maximal field value: %%_value.', ['value' => $default->attributes['max']]));
    if ($this->description)                                                           $return[] = new markup('p', [], $this->description);
    if (count($return)) {
      return (new markup($this->description_tag_name, [], $return))->render();
    }
  }

}}