<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\modules\storage\storage_factory as storage;
          class form_field_captcha extends \effectivecore\form_field {

  public $title = 'Captcha';
  public $g_length = 6;
  public $g_characters = [];
  public $attributes = ['class' => ['captcha' => 'captcha']];
  public static $glyphs;

  static function init() {
    foreach (storage::get('settings')->select_group('captcha') as $c_settings) {
      foreach ($c_settings as $c_characters) {
        foreach ($c_characters as $c_character) {
          foreach ($c_character->glyphs as $c_glyph) {
            static::$glyphs[$c_glyph] = $c_character->character;
          }
        }
      }
    }
  }

  function build() {
    if (!static::$glyphs) static::init();
    $canvas = new canvas_svg(5 * $this->g_length, 15, 5);
    $canvas->fill('#000000', .9);
    for ($i = 0; $i < $this->g_length; $i++) {
      $c_glyph = array_rand(static::$glyphs);
      $this->g_characters[] = static::$glyphs[$c_glyph];
      $canvas->glyph_set(
        rand(0, 2) - 1 + ($i * 5),
        rand(1, 5), $c_glyph
      );
    }
    $this->child_insert($canvas, 'captcha');
    $this->child_insert(new markup('input', ['size' => $this->g_length]), 'element');
  }

  function build_demo() {
    $canvas = new canvas_svg(105, 15, 5);
    $canvas->fill('#ffffff');
    $canvas->glyph_set( 5, 3, '01110|10001|10001|10001|10001|10001|10001|10001|10001|01110'); # 0
    $canvas->glyph_set(15, 3, '00001|00001|00001|00001|00001|10001|01001|00101|00010|00001'); # 1
    $canvas->glyph_set(25, 3, '11111|10000|01000|00100|00010|00001|00001|00001|00001|11110'); # 2
    $canvas->glyph_set(35, 3, '01000|00100|00010|00001|11111|01000|00100|00010|00001|11111'); # 3
    $canvas->glyph_set(45, 3, '00001|00001|00001|00001|01111|10001|01001|00101|00010|00001'); # 4
    $canvas->glyph_set(55, 3, '01000|00100|00010|00001|01111|10000|10000|10000|10000|01111'); # 5
    $canvas->glyph_set(65, 3, '01110|10001|10001|10001|10001|01110|10000|01000|00100|00010'); # 6
    $canvas->glyph_set(75, 3, '10000|10000|10000|10000|10000|01000|00100|00010|00001|11111'); # 7
    $canvas->glyph_set(85, 3, '01110|10001|10001|10001|10001|01110|10001|10001|10001|01110'); # 8
    $canvas->glyph_set(95, 3, '01000|00100|00010|00001|01110|10001|10001|10001|10001|01110'); # 9
    return $canvas;
  }

}}