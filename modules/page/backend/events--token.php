<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\page {
          use \effectivecore\modules\storage\storages_factory as storages;
          abstract class events_token extends \effectivecore\events_token {

  static function on_color_get($match, $arg_1_num = null) {
    $decoration = storages::get('settings')->select('decoration');
    $colors     = storages::get('settings')->select('colors');
    switch ($match) {
      case '%%_color'   : return $colors['page'][ $decoration['page']->color_id    ]->value;
      case '%%_color_bg': return $colors['page'][ $decoration['page']->color_bg_id ]->value;
    }
  }

}}