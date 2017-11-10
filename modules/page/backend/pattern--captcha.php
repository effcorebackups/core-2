<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\modules\storage\storage_factory as storage;
          class captcha extends \effectivecore\node_simple {

  public $length = 8;

  function render() {
    $characters = storage::get('settings')->select_group('captcha')['page']->characters;
    $canvas = new canvas_svg(40, 15, 5);
    $canvas->fill('#000000', .9);
    $canvas->glyph_set(rand(0,   1), rand(1, 5), $characters['ch0']->glyphs['default']);
    $canvas->glyph_set(rand(4,   5), rand(1, 5), $characters['ch1']->glyphs['default']);
    $canvas->glyph_set(rand(9,  11), rand(1, 5), $characters['ch2']->glyphs['default']);
    $canvas->glyph_set(rand(14, 16), rand(1, 5), $characters['ch3']->glyphs['default']);
    $canvas->glyph_set(rand(19, 21), rand(1, 5), $characters['ch4']->glyphs['default']);
    $canvas->glyph_set(rand(24, 26), rand(1, 5), $characters['ch5']->glyphs['default']);
    $canvas->glyph_set(rand(29, 31), rand(1, 5), $characters['ch6']->glyphs['default']);
    $canvas->glyph_set(rand(34, 36), rand(1, 5), $characters['ch7']->glyphs['default']);
    return $canvas->render();
  }

}}