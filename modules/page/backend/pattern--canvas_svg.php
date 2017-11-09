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
  public $scale;
  public $canvas = [];

  function __construct($w = 10, $h = 10, $scale = 1, $color_bg = 'white', $weight = 0) {
    if ($w) $this->w = $w;
    if ($h) $this->h = $h;
    if ($scale)    $this->scale    = $scale;
    if ($color_bg) $this->color_bg = $color_bg;
    parent::__construct([], $weight);
  }

  function pixel_set($x, $y, $color = '#000000') {
    $this->canvas[$x][$y] = $color;
  }
  function pixel_get($x, $y) {
    return isset($this->canvas[$x][$y]) ?
                 $this->canvas[$x][$y] : null;
  }

  function fill_noise() {
    for ($c_x = 0; $c_x < $this->w; $c_x++) {
    for ($c_y = 0; $c_y < $this->h; $c_y++) {
      if (rand(0, 1) >= .5) {
        $this->pixel_set($c_x, $c_y, '#000000');
      }
    }}
  }

  function render() {
    return (new template($this->template, [
      'color_bg' => $this->color_bg,
      'width'    => $this->scale * $this->w,
      'height'   => $this->scale * $this->h,
      'canvas'   => $this->render_canvas($this->canvas),
    ]))->render();
  }

  function render_canvas($canvas) {
    $return = [];
    foreach ($this->canvas as $c_x => $x_row) {
      foreach ($x_row as $c_y => $c_color) {
        $return[] = (new markup_xml_simple('rect', [
          'style'  => 'fill:'.$c_color,
          'x'      => 1 * $this->scale * $c_x,
          'y'      => 1 * $this->scale * $c_y,
          'width'  => 1 * $this->scale,
          'height' => 1 * $this->scale
        ]))->render();
      }
    }
    return implode(nl, $return);
  }

}}