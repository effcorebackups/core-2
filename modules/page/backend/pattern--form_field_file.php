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
    $this->description = translation::get('Maximal file size: %%_value.', [
      'value' => locale::format_human_bytes($this->get_max_file_size())
    ]);
  }

  function get_max_file_size() {
    return $this->max_file_size ?
       min($this->max_file_size, factory::human_to_bytes(ini_get('upload_max_filesize'))) :
                                 factory::human_to_bytes(ini_get('upload_max_filesize'));
  }

  function tmp_file_push_to_wait_list($file_name) {
    $this->child_insert(new markup_simple('input', [
      'type'  => 'hidden',
      'name'  => 'wait_list[]',
      'value' => $file_name
    ]));
  }

}}