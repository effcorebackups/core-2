<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          class captcha extends \effectivecore\node_simple {

  public $length = 8;

  function render() {
    $canvas = new canvas_svg(40, 15, 5);
    $canvas->fill_noise();
    return $canvas->render();
  }

}}