<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\translations_factory as translations;
          class form_field extends \effectivecore\form_box {

  public $tag_name = 'x-form-field';

  function render_description() {
    $return = [];
    $default = $this->child_select('default');
    if ($default && !empty($default->attributes['minlength'])) $return[] = new markup('p', ['class' => ['minlength' => 'minlength']], translations::get('Field must contain a minimum of %%_lenght characters.', ['lenght' => $default->attributes['minlength']]));
    if ($default && !empty($default->attributes['maxlength'])) $return[] = new markup('p', ['class' => ['maxlength' => 'maxlength']], translations::get('Field must contain a maximum of %%_lenght characters.', ['lenght' => $default->attributes['maxlength']]));
    if ($this->description)                                    $return[] = new markup('p', [], $this->description);
    if (count($return)) {
      return (new markup('x-description', [], $return))->render();
    }
  }

}}