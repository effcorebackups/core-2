<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effectivecore {
          class form_container_license_agreement extends \effectivecore\form_container {

  public $tag_name = 'fieldset';
  public $title = 'License agreement';
  public $title_tag_name = 'legend';

  function build() {
    $lang_code = language::get_current();
    $license_file = new file(dir_root.'license-'.$lang_code.'.md');
    $license_markup = markdown::markdown_to_markup($license_file->load());
    $this->child_insert(new markup('x-license', [], $license_markup), 'license_markup');
    $this->child_insert(new form_container_checkboxes(['is_agree' => 'agree'], ['is_agree' => 'is_agree']), 'is_agree');
    $this->child_select('is_agree')->build();
  }

}}