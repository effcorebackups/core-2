<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effectivecore\modules\page {
          use \effectivecore\storage as storage;
          abstract class events_token extends \effectivecore\events_token {

  static function on_color_get($match, $arg_1_num = null) {
    $decoration = storage::get('files')->select_group('decoration');
    $colors     = storage::get('files')->select_group('colors');
    switch ($match) {
      case '%%_color'   : return $colors['page'][ $decoration['page']->color_id    ]->value;
      case '%%_color_bg': return $colors['page'][ $decoration['page']->color_bg_id ]->value;
    }
  }

}}