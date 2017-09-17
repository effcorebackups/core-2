<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\translations_factory as translations;
          use \effectivecore\modules\storage\storages_factory as storages;
          class form_container_palette extends \effectivecore\form_container_radios {

  function build() {
    $this->attribute_insert('class', ['palette' => 'palette']);
    foreach (storages::get('settings')->select('colors') as $module_id => $c_colors) {
      foreach ($c_colors as $c_color_id => $c_color_info) {
        $this->radio_insert('', [
          'value' => $c_color_id,
          'title' => translations::get('Color ID = %%_id (value = %%_value)', ['id' => $c_color_id, 'value' => $c_color_info->value]),
          'style' => ['background-color: '.$c_color_info->value]
        ]);
      }
    }
  }

}}