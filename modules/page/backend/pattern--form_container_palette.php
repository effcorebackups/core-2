<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effectivecore {
          class form_container_palette extends \effectivecore\form_container_radios {

  function build() {
    $this->attribute_insert('class', ['palette' => 'palette']);
    foreach (storage::get('settings')->select_group('colors') as $module_id => $c_colors) {
      foreach ($c_colors as $c_color_id => $c_color_info) {
        $this->input_insert(null, [
          'value' => $c_color_id,
          'title' => translation::get('Color ID = %%_id (value = %%_value)', ['id' => $c_color_id, 'value' => $c_color_info->value]),
          'style' => ['background-color: '.$c_color_info->value]
        ], $c_color_id);
      }
    }
  }

}}