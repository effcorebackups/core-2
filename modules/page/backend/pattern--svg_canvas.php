<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          class svg_canvas extends \effectivecore\node_simple {

  public $template = 'svg_canvas';
  public $color_bg = 'white';
  public $width = 100;
  public $height = 200;
  public $canvas = [];

  function pixel_set($x, $y, $color = '#000000') {
    $this->canvas[$x][$y] = $color;
  }

  function render() {
    return (new template($this->template, [
      'color_bg' => $this->color_bg,
      'width'    => $this->width,
      'height'   => $this->height,
      'canvas'   => $this->render_canvas($this->canvas),
    ]))->render();
  }

  function render_canvas($canvas) {
    $return = [];
    foreach ($this->canvas as $x => $x_row) {
      foreach ($x_row as $y => $c_color) {
        $return[] = (new markup_xml_simple('rect', [
          'style' => 'fill:'.$c_color,
          'x' => $x * 10,
          'y' => $y * 10,
          'width'  => 10,
          'height' => 10
        ]))->render();
      }
    }
    return implode(nl, $return);
  }

}}