<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          class svg extends \effectivecore\node_simple {

  public $template = 'svg';
  public $color_bg = 'white';
  public $width = 0;
  public $height = 0;
  public $canvas = [];

  function __construct($attributes = [], $weight = 0) {
    parent::__construct($attributes, $weight);
  }

  function pixel_set($x, $y, $state = 1) {
    $this->canvas[$x][$y] = $state;
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
      foreach ($x_row as $y => $c_value) {
        $return[] = (new markup_xml_simple('rect', [
          'style' => 'fill:#000000',
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