<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          class canvas_svg extends \effectivecore\node_simple {

  public $template = 'canvas_svg';
  public $w;
  public $h;
  public $color_bg;
  public $canvas = [];

  function __construct($w = 100, $h = 100, $color_bg = 'white', $weight = 0) {
    if ($w) $this->w = $w;
    if ($h) $this->h = $h;
    if ($color_bg) $this->color_bg = $color_bg;
    parent::__construct([], $weight);
  }

  function pixel_set($x, $y, $color = '#000000') {
    $this->canvas[$x][$y] = $color;
  }

  function render() {
    return (new template($this->template, [
      'color_bg' => $this->color_bg,
      'width'    => $this->w,
      'height'   => $this->h,
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