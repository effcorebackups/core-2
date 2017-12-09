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
    $this->manager_build();
    $this->description = translation::get('Maximal file size: %%_value.', [
      'value' => locale::format_human_bytes($this->get_max_file_size())
    ]);
  }

  function get_max_file_size() {
    return $this->max_file_size ?
       min($this->max_file_size, factory::human_to_bytes(ini_get('upload_max_filesize'))) :
                                 factory::human_to_bytes(ini_get('upload_max_filesize'));
  }

  function manager_build() {
    $this->child_insert(new form_container_checkboxes(), 'manager');
    $this->child_select('manager')->build();
  }

  function manager_insert_action($info, $hash) {
    $full_name = $this->child_select('element')->attribute_select('name');
    $this->child_select('manager')->input_insert(
      translation::get('delete file: %%_name', ['name' => $info->name]), ['name' => 'manager_delete_'.$full_name, 'value' => $hash]
    );
  }

  function manager_clean() {
    $this->manager_build();
  }

}}