<?php

  ##################################################################
  ### Copyright © 2017—2020 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effcore {
          class part_preset_link {

  public $id;
  public $weight = 0;

  function __construct($id = null, $weight = 0) {
    if ($id    ) $this->id     = $id;
    if ($weight) $this->weight = $weight;
  }

  function page_part_preset_get() {
    return page_part_preset::select($this->id);
  }

  function page_part_make() {
    $preset = $this->page_part_preset_get();
    if (isset($preset)) {
      return $preset->page_part_make();
    }
  }

}}