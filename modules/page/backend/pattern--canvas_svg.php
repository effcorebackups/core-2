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

  function pixel_get($x, $y) {
    return isset($this->canvas[$y][$x]) ?
                 $this->canvas[$y][$x] : null;
  }

  function pixel_set($x, $y, $color = '#000000') {
    $this->canvas[$y][$x] = $color;
  }

  function matrix_set($x, $y, $matrix) {
    foreach ($matrix as $c_y => $y_row) {
      foreach ($y_row as $c_x => $c_color) {
        $this->pixel_set($c_x + $x, $c_y + $y, $c_color);
      }
    }
  }

  function glyph_set($x, $y, $data, $inversion = false) {
    $rows = explode('|', $data);
    for ($c_y = 0; $c_y < count($rows); $c_y++) {
      for ($c_x = 0; $c_x < strlen($rows[$c_y]); $c_x++) {
        $new_color = $rows[$c_y][$c_x] == '1' ? '#000000' : null;
        if ($inversion && $new_color) {
          $old_color = $this->pixel_get($c_x, $c_y);
          $new_color = ['#000000' => '#ffffff',
                        '#ffffff' => '#000000', null => '#000000'][$old_color];
        }
        if ($new_color) {
          $this->pixel_set($c_x + $x, $c_y + $y, $new_color);
        }
      }
    }
  }

  function fill($color, $random = 0) {
    for ($c_x = 0; $c_x < $this->w; $c_x++) {
    for ($c_y = 0; $c_y < $this->h; $c_y++) {
      if (!$random || ($random && rand(0, 10) / 10 > $random)) {
        $this->pixel_set($c_x, $c_y, $color);
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
    foreach ($this->canvas as $c_y => $y_row) {
      foreach ($y_row as $c_x => $c_color) {
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