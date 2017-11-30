<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\locale_factory as locale;
          use \effectivecore\translation_factory as translation;
          class form_field_file extends \effectivecore\form_field {

  public $max_file_size;

  function build() {
    $this->description = translation::get('Maximal file size: %%_value.', ['value' => $this->get_max_file_size()]);
  }

  function get_max_file_size() {
    return $this->max_file_size ? locale::format_human_bytes($this->max_file_size) : ini_get('upload_max_filesize');
  }

}}