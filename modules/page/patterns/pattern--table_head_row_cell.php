<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          class table_head_row_cell extends node {

  public $template = 'table_head_row_cell';

  function child_insert($child, $id = null) {
    return parent::child_insert(
      is_string($child) ? new text($child) : $child, $id
    );
  }

}}