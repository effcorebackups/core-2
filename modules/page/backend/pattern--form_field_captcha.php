<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\modules\storage\storage_factory as storage;
          class form_field_captcha extends \effectivecore\form_field {

  public $title = 'Captcha';
  public $description = 'Write the characters from the picture.';
  public $attributes = ['class' => ['captcha' => 'captcha']];

  public $g_length = 6;
  public $g_characters = '';
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
    $canvas->fill('#000000', 0, 0, null, null, .9);
    for ($i = 0; $i < $this->g_length; $i++) {
      $c_glyph = array_rand(static::$glyphs);
      $this->g_characters.= static::$glyphs[$c_glyph];
      $this->glyph_set($canvas, rand(0, 2) - 1 + ($i * 5), rand(1, 5), $c_glyph);
    }
  # build form elements
    $this->child_insert($canvas, 'canvas');
    $this->child_insert(new markup_simple('input', [
      'type' => 'text',
      'name' => 'captcha',
      'size' => $this->g_length,
      'required' => 'required',
      'minlength' => $this->g_length,
      'maxlength' => $this->g_length
    ]), 'element');
  }

  function glyph_set($canvas, $x, $y, $data) {
    $rows = explode('|', $data);
    for ($c_y = 0; $c_y < count($rows); $c_y++) {
      for ($c_x = 0; $c_x < strlen($rows[$c_y]); $c_x++) {
        $c_color = $rows[$c_y][$c_x] == '1' ? '#000000' : null;
        if ($c_color) {
          $canvas->pixel_set($c_x + $x, $c_y + $y, $c_color);
        }
      }
    }
  }

}}