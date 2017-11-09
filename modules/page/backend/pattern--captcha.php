<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          class captcha extends \effectivecore\node_simple {

  public $length = 8;

  function render() {
    $canvas = new svg_canvas();
    $canvas->width = 200;
    $canvas->height = 150;
    $canvas->pixel_set(0, 0);
    $canvas->pixel_set(2, 0);
    $canvas->pixel_set(4, 0);
    return $canvas->render();
  }

}}