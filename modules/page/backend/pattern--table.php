<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          class table extends \effectivecore\markup {

  public $tag_name = 'table';

  function __construct($attributes = [], $tbody = [], $thead = [], $weight = 0) {
    parent::__construct($this->tag_name, $attributes, null, $weight);
    if ($thead) $this->child_insert(new table_head([], $thead), 'head');
    if ($tbody) $this->child_insert(new table_body([], $tbody), 'body');
  }

}}