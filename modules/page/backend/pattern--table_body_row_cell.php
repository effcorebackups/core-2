<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          class table_body_row_cell extends \effectivecore\node {

  public $template = 'table_body_row_cell';

  function child_insert($child, $id = null) {
    return parent::child_insert(
      is_string($child) || is_numeric($child) ? new text($child) : $child, $id
    );
  }

}}