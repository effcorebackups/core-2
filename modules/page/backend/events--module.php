<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\page {
          use \effectivecore\modules\page\pages_factory as page;
          abstract class events_module extends \effectivecore\events_module {

  static function on_start() {
    return page::find_and_render();
  }

}}